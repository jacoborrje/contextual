<?php
namespace App\Utils\Analysis;

use App\Entity\Source;
use App\Entity\Topic;
use App\Entity\Actor;
use App\Utils\ActorService;
use App\Utils\Analysis\TextAnalysisService;

use Doctrine\ORM\EntityManagerInterface;
use Phpml\Classification\KNearestNeighbors;
use Phpml\ModelManager;
use Phpml\Regression\LeastSquares;
use TextAnalysis\Analysis\FreqDist;
use TextAnalysis\Tokenizers\GeneralTokenizer;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;
Use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class TopicService
{
    private $sourceTopicMatrix = [];
    private $allSources, $allTopics;
    protected $keys;
    protected $classifiers;
    protected $predictions;
    protected $logger;
    protected $sourceRepository, $topicsRepository, $tokenizer, $modelsDir;
    protected $modelManager;
    protected $jsonpath;
    protected $allActors;
    private $actorService, $textAnalysisService;

    function __construct($modelsDir, EntityManagerInterface $entityManager, LoggerInterface $logger, ActorService $actorService, TextAnalysisService $textAnalysisService)
    {
        $this->logger = $logger;
        $this->modelsDir = $modelsDir;
        $this->tokenizer = new GeneralTokenizer();
        $this->jsonpath = $this->modelsDir.'key';

        $this->sourceRepository = $entityManager->getRepository(Source::class);
        $this->topicsRepository = $entityManager->getRepository(Topic::class);
        $this->actorRepository = $entityManager->getRepository(Actor::class);
        $this->actorService = $actorService;
        $this->textAnalysisService = $textAnalysisService;

        $this->allSources = $this->sourceRepository->findAllSourcesWithTopicsAndTranscription();
        $this->logger->info('Found  '.count($this->allSources).' sources with topics and transcriptions.');
        $this->allTopics = $this->topicsRepository->findAll();
        $this->logger->info('Found  '.count($this->allTopics).' topics.');
        $this->modelManager = new ModelManager();
        $this->loadKeys();
    }

    function loadKeys(){
        for($language = 1;$language < 6; $language++) {
            if (file_exists($this->jsonpath."_".$language.".json")) {
                $this->logger->info('Loading prediction keys from JSON');
                $this->keys = json_decode(file_get_contents($this->jsonpath."_".$language.".json"), true);
            } else {
                $this->logger->info('Prediction keys file not found. Creating new keys.');
                $this->prepareKeys();
            }
        }
    }

    function prepareKeys(){
        $this->keys = [];
        $this->logger->info('Preparing prediction matrixes.');

        for($language = 1;$language < 6; $language++) {
            $languageSources = $this->sourceRepository->findAllSourcesWithTopicsAndTranscriptionAndLanguage($language);
            $this->keys = [];



            foreach ($languageSources as $source) {
                if ($source->getTranscription() !== "" && !is_null($source->getTranscription())) {
                    $raw_transcription = $this->textAnalysisService->cleanUpTranscription($source->getTranscription());
                    $sourceTokens = $this->tokenizer->tokenize($raw_transcription);
                    $freqDist = new FreqDist($sourceTokens);
                    $this->keys = array_merge($this->keys, $freqDist->getKeys());
                }
            }
            $this->keys = $this->textAnalysisService->removeNames($this->keys);

            natsort($this->keys);
            $this->keys = array_unique($this->keys);

            //Load stopwords
            $stopwords = $this->textAnalysisService->loadStopwords($language);

            if(!is_null($stopwords)) {
                foreach ($stopwords as $stopword => $frequency) {
                    $stopword_key = array_search ( $stopword , $this->keys );
                    if($stopword_key){
                        $this->logger->info('Removing stopword: '.$stopword);
                        unset($this->keys[$stopword_key]);
                    }
                }
            }
            file_put_contents($this->jsonpath."_".$language.".json", json_encode($this->keys, JSON_UNESCAPED_UNICODE));
            $this->logger->info('Found ' . count($this->keys) . ' unique words in transcriptions.');
        }
    }

    function makeTopicSuggestions(Source $source)
    {
        $language = $source->getLanguage();
        $testSource = $source;
        $testTokens = $this->tokenizer->tokenize($testSource->getTranscription());
        if(empty($testTokens)){
            return null;
        }
        $freqDist = new FreqDist($testTokens);
        $testValues = $freqDist->getKeyValuesByWeight();
        $j = 0;
        $topicSamples = [];

        foreach ($this->keys as $key) {
            if (array_key_exists($key, $testValues)) {
                $topicSamples[$j] = $testValues[$key];
            } else {
                $topicSamples[$j] = 0;
            }
            $j++;
        }

        $topicPredictions = [];
        $i = 0;
        foreach($this->allTopics as $topic){
            $topicId = $topic->getId();
            $topicClassifier = $this->loadModel($topicId, $language);
            if(!is_null($topicClassifier)) {
                $topicPredictions[$topicId] = $topicClassifier->predict($topicSamples);
            }
            else{
                $topicPredictions[$topicId] = 0;
            }
            $i++;
        }


        $predictedTopics = [];
        foreach($topicPredictions as $predictedId => $prediction){
            if($prediction === 1){
                $suggestedTopic = $this->topicsRepository->find($predictedId);
                //if(!$source->hasTopic($suggestedTopic)) {
                if(true) {
                    $predictedTopics[] = $suggestedTopic;
                }
            }
        }
        $predictedTopicNames = [];

        foreach($predictedTopics as $topic){
            $predictedTopicNames[] = $topic->__toString();
        }
        $this->logger->info('Topic predictions:' . print_r($predictedTopicNames, true));
        return $predictedTopics;
    }

    function countSourcesWithTopics(){
        return count($this->allSources);
    }

    function transpose($matrix){
        return array_map(null, ...$matrix);
    }

    function trainModels(){
        for($language = 0; $language < 6; $language++) {
            $languageSources = $this->sourceRepository->findAllSourcesWithTopicsAndTranscriptionAndLanguage($language);

            if (count($languageSources) > 0) {
                $samples = [];
                $this->predictions = [];

                $this->logger->info("Updating keys array");
                $this->prepareKeys();

                $this->classifiers = [];
                $this->logger->info("Training regressions");


                $i = 0;
                foreach ($languageSources as $source) {
                    $sourceTokens = $this->tokenizer->tokenize($source->getTranscription());
                    $freqDist = new FreqDist($sourceTokens);
                    $sourceValues = $freqDist->getKeyValuesByWeight();
                    $j = 0;
                    foreach ($this->keys as $key) {
                        if (array_key_exists($key, $sourceValues)) {
                            $samples[$i][$j] = $sourceValues[$key];
                        } else {
                            $samples[$i][$j] = 0;
                        }
                        $j++;
                    }
                    $i++;
                }
                $languageTopics = [];
                $languageTopicIDs = [];

                foreach($languageSources as $source){
                    $topics = $source->getSourceTopics();
                    foreach($topics as $topic) {
                        $topic = $topic->getTopic();
                        if (!in_array($topic->getId(), $languageTopicIDs)) {
                            Echo "Topic ". $topic->getId(). " is not in array!<br>";
                            $languageTopics[] = $topic;
                            $languageTopicIDs[] = $topic->getId();
                        }
                    }
                }

                echo "[";
                foreach ($languageTopics as $topic){
                    echo $topic->getId(). ", ";
                }
                echo "]";

                $targets = [];
                foreach ($languageTopics as $topic) {
                    $topicId = $topic->getId();
                    $j = 0;
                    foreach ($languageSources as $source) {
                        if ($source->hasTopic($topic)) {
                            $targets[$topicId][$j] = 1;
                        } else {
                            $targets[$topicId][$j] = 0;
                        }
                        $j++;
                    }
                }

                foreach ($languageTopics as $topic) {
                    $topicId = $topic->getId();
                    $topicTarget = $targets[$topicId];
                    //$logger->info('TopicTarget (' . $topic . '):' . print_r($topicTarget, true));
                    if (array_sum($topicTarget) === 0) {
                        $this->classifiers[$topicId] = null;
                    } else {
                        $this->classifiers[$topicId] = new SVC(Kernel::RBF, $cost = 1000, $degree = 3, $gamma = 6);
                        $this->classifiers[$topicId]->train($samples, $targets[$topicId]);
                        $this->logger->info('Finished training topic  "' . $topic . '" for language ' . $language . '.');
                    }
                }
                foreach ($this->classifiers as $topicId => $classifier) {
                    if (!is_null($classifier)) {
                        $filepath = $this->modelsDir . $topicId . "_lang_" . $language . ".svc";
                        $this->modelManager->saveToFile($classifier, $filepath);
                    }
                }
                $this->logger->info('Samples array dimensions: ' . count($samples) . 'x' . count(reset($samples)));
                $this->logger->info('Targets array dimensions: ' . count($targets) . 'x' . count(reset($targets)));
                $this->logger->info('Retrained topic models successfully for language ' . $language . '.');
            }
        }
    }



    function loadModel($topicId, $language){
        $filepath = $this->modelsDir . $topicId . "_lang_".$language.".svc";
        if(file_exists ( $filepath )){
            return $this->modelManager->restoreFromFile($filepath);
        }
        else{
            return null;
        }
    }
}
?>