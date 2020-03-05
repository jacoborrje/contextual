<?php
namespace App\Utils\Analysis;

use App\Entity\Source;
use App\Entity\Topic;
use App\Entity\Actor;
use App\Utils\ActorService;
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
    private $actorService;

    function __construct($modelsDir, EntityManagerInterface $entityManager, LoggerInterface $logger, ActorService $actorService)
    {
        $this->logger = $logger;
        $this->modelsDir = $modelsDir;
        $this->tokenizer = new GeneralTokenizer();
        $this->jsonpath = $this->modelsDir.'key';

        $this->sourceRepository = $entityManager->getRepository(Source::class);
        $this->topicsRepository = $entityManager->getRepository(Topic::class);
        $this->actorRepository = $entityManager->getRepository(Actor::class);
        $this->actorService = $actorService;

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

            //Load stopwords
            $stopwords = null;
            if (file_exists($this->modelsDir."stopwords_for_lang_".$language.".yaml")) {
                $this->logger->info('Found file "'.$this->modelsDir."stopwords_for_lang_".$language.".yaml");
                $stopwords = Yaml::parseFile($this->modelsDir."stopwords_for_lang_".$language.".yaml");
                $this->logger->info('Loaded stopwords:' . print_r($stopwords, true));
            }
            else{
                $this->logger->info('Did not find stopword file: "'.$this->modelsDir."stopwords_for_lang_".$language.".yaml");
            }

            foreach ($languageSources as $source) {
                if ($source->getTranscription() !== "" && !is_null($source->getTranscription())) {
                    $raw_transcription = $this->cleanUpTranscription($source->getTranscription());
                    $sourceTokens = $this->tokenizer->tokenize($raw_transcription);
                    $freqDist = new FreqDist($sourceTokens);
                    $this->keys = array_merge($this->keys, $freqDist->getKeys());
                }
            }
            $this->keys = $this->removeNames($this->keys);

            natsort($this->keys);
            $this->keys = array_unique($this->keys);

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

    function removeNames($keys, $searchKeys = false){
        $allNames = $this->actorService->getAllActorNames();
        if($searchKeys) {
            $keys = array_flip($keys);
        }
        $isUTF8 = true;
        foreach ($keys as $key){
            if(!mb_detect_encoding($key, 'UTF-8')){
                $isUTF8 = false;
                $this->logger->info($key.' is not UTF-8!');
            }
        }
        if(!$isUTF8)
            $this->logger->info("Error! Not all keys are encoded as UTF-8!");
        else
            $this->logger->info("All keys are encoded as UTF-8!");

        //$this->logger->info(print_r($keys,true));
        $this->logger->info('Searching for ' . count($allNames)." names among ".count($keys)." keys.");
        $test = array_search("alström", $keys);
        if($test !== false) $this->logger->info("Keys array contains the name Alström");
        $test = array_search("jacob", $keys);
        if($test !== false) $this->logger->info("Keys array contains the name Jacob");
        foreach ($allNames as $name) {
            $name = mb_strtolower($name,'UTF-8');
            //$this->logger->info("Searching for name: ".$name);
            //$this->logger->info("Encoding of name is ".mb_detect_encoding($name, 'UTF-8'));
            $search = array_search($name, $keys);
            if ($search !== false) {
                $this->logger->info('Removing name: ' . $name." from array.");
                unset($keys[$search]);
            }
            $search = array_search($name.'s', $keys);
            if ($search !== false) {
                $this->logger->info('Removing name: ' . $name."s from array.");
                unset($keys[$search]);
            }
        }
        if($searchKeys){
            $keys = array_flip($keys);

        }
        return $keys;
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

    function generateStopwordSuggestions(){
        for($language = 1;$language < 6; $language++) {
            $languageSources = $this->sourceRepository->findAllSourcesWithTopicsAndTranscriptionAndLanguage($language);
            $langFrequencies = [];

            foreach ($languageSources as $source) {
                if ($source->getTranscription() !== "" && !is_null($source->getTranscription())) {
                    $raw_transcription = $this->cleanUpTranscription($source->getTranscription());
                    $sourceTokens = $this->tokenizer->tokenize($raw_transcription);
                    $freqDist = new FreqDist($sourceTokens);
                    $frequencies = $freqDist->getKeyValuesByFrequency();

                    foreach (array_keys($frequencies + $langFrequencies) as $key) {
                        $langFrequencies[$key] = (isset($frequencies[$key]) ? $frequencies[$key] : 0) + (isset($langFrequencies[$key]) ? $langFrequencies[$key] : 0);
                    }
                }
            }
            natsort($langFrequencies);
            $langFrequencies = array_reverse($langFrequencies, true);
            file_put_contents($this->modelsDir."suggested_stopwords_for_lang_".$language.".yaml", Yaml::dump($langFrequencies, 1));
        }
    }

    function cleanUpTranscription($transcription){
        //$this->logger->info("Transcription character encoding is: ");
        $transcription = html_entity_decode($transcription);
        $transcription = strip_tags($transcription, '<br>');
        $transcription = preg_replace('/\<br\s?\>/',' ' ,$transcription);
        $transcription = preg_replace('/\<br\s?\/\>/',' ' ,$transcription);
        $transcription = mb_strtolower ($transcription, 'UTF-8');
        $transcription = preg_replace('/[\n]+/u',' ' ,$transcription);
        $transcription = preg_replace('/[^\p{L}\s]+/u','' ,$transcription);
        $transcription = preg_replace('/[\fз]+/u','' ,$transcription);
        $transcription = preg_replace('/[\xC2\xA0]+/u',' ' ,$transcription);
        $transcription = preg_replace('/divsignature/u',' ' ,$transcription);
        $transcription = preg_replace('/divaddress/u',' ' ,$transcription);
        $transcription = preg_replace('/divstartdate/u',' ' ,$transcription);

        //$this->logger->info('Clean transcription: ' . $transcription);

        return $transcription;
    }

    function generateLemmatizationDraft(){
        for($language = 1;$language < 6; $language++) {
            $keys = [];
            $languageSources = $this->sourceRepository->findAllSourcesWithTopicsAndTranscriptionAndLanguage($language);
            $langFrequencies = [];

            foreach ($languageSources as $source) {
                if ($source->getTranscription() !== "" && !is_null($source->getTranscription())) {
                    $raw_transcription = $this->cleanUpTranscription($source->getTranscription());
                    $sourceTokens = $this->tokenize($raw_transcription);
                    $keys = array_merge($keys, $sourceTokens);
                }
            }
            $keys = array_unique($keys);

            $numberOfKeys1 = count($keys);
            $keys = $this->removeNames($keys, false);
            $numberOfKeys2 = count($keys);

            $this->logger->info('Reduced number of keys from '.$numberOfKeys1." to ".$numberOfKeys2.' in lemmatization draft.');

            $output_array = [];

            foreach ($keys as $key => $value){
                $output_array[$value] = $value;
            }
            natsort($output_array);
            file_put_contents($this->modelsDir."Lemmatization_draft_for_lang_".$language.".yaml", Yaml::dump($output_array, 1));
        }
    }

    function tokenize($string){
        $token_array = preg_split( "/[\s\r\f]+/", $string);
        return $token_array;
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