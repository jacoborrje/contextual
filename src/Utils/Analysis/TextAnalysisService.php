<?php


namespace App\Utils\Analysis;


use App\Entity\Actor;
use App\Entity\Source;
use App\Entity\Topic;
use App\Utils\ActorService;
use Doctrine\ORM\EntityManagerInterface;
use Phpml\ModelManager;
use Symfony\Component\Yaml\Yaml;
use TextAnalysis\Analysis\FreqDist;
use TextAnalysis\Tokenizers\GeneralTokenizer;
Use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class TextAnalysisService
{
    protected $logger;
    protected $sourceRepository, $tokenizer, $modelsDir;
    protected $jsonpath;
    protected $foundWordForms;

    function __construct($modelsDir, EntityManagerInterface $entityManager, LoggerInterface $logger, ActorService $actorService)
    {
        $this->logger = $logger;
        $this->modelsDir = $modelsDir;
        $this->tokenizer = new GeneralTokenizer();
        $this->jsonpath = $this->modelsDir.'key';

        $this->sourceRepository = $entityManager->getRepository(Source::class);
        $this->actorRepository = $entityManager->getRepository(Actor::class);
        $this->actorService = $actorService;

        $this->foundWordForms = 0;

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

            //Load stopwords
            $stopwords = $this->loadStopwords($language);

            if(!is_null($stopwords)) {
                foreach ($stopwords as $stopword => $frequency) {
                    $stopword_keys = array_keys ( $keys, $stopword);
                    if(!is_null($stopword_keys)){
                        foreach($stopword_keys as $stopword_key){
                            $this->logger->info('Removing stopword: '.$stopword);
                            unset($this->keys[$stopword_key]);
                        }
                    }
                }
            }

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

            $output_array = $this->buildLemmaTable($output_array, $language);


            natsort($output_array);
            file_put_contents($this->modelsDir."Lemmatization_draft_for_lang_".$language.".yaml", Yaml::dump($output_array, 1));
        }
    }


    function buildLemmaTable($tokens, $language){
        if($language == 1) {
            $tokens = $this->reduceSpellingVariation($tokens, $language);

            foreach ($tokens as $token => $word) {
                if (mb_substr($word, -1) !== 'a' && $token === $word) {
                    $tokens = $this->groupNounForms($word, $tokens, $word);
                }
                else if (mb_substr($word, -1) === 'a' && $token === $word) {
                    $stem = mb_substr($word, 0, -1);
                    $tokens = $this->groupVerbForms($stem, $tokens, $stem);
                }
            }
            $this->logger->info("Found ".$this->foundWordForms." word forms, which have been reduced to their stems.");
            $this->foundWordForms = 0;
            return $tokens;
        }
        else
            return $tokens;
    }

    function reduceSpellingVariation($tokens, $language){
        if($language == 1) {
            $spellingVariation = [
                                    ['å', 'o'],
                                    ['ä', 'e'],
                                    ['v', 'w'],
                                    ['ck', 'ch'],
                                    ['kan','kian'],
                                    ['i', 'ij'],
                                    ['j', 'i'],
                                    ['e', 'ie'],
                                    ['v', 'fv'],
                                    ['ju', 'iu'],
                                    ['e', 'é'],
                                    ['a', 'ah']
                                 ];

            foreach ($tokens as $token => $word) {
                foreach($spellingVariation as $variation) {
                    if (mb_stripos($word, $variation[0]) !== false) {
                        $altSpelling = str_replace($variation[0], $variation[1], $word);
                        if(in_array($altSpelling, $tokens)){
                            $tokens[$altSpelling] = $word;
                        }
                        $wordForms = array_keys($tokens, $altSpelling);
                        foreach($wordForms as $wordForm){
                            $tokens[$wordForm] = $word;
                        }
                    }
                    $variationOccurances = $this->strpos_all($word, $variation[0]);
                    if(count($variationOccurances)>0){
                        foreach($variationOccurances as $occurance){
                            $altSpelling = substr_replace($word, $variation[1], $occurance, strlen($variation[0]));
                            if(in_array($altSpelling, $tokens)){
                                $tokens[$altSpelling] = $word;
                            }
                        }
                    }
                }
            }
        }
        return $tokens;
    }

    function groupVerbForms($stem, $tokens, $originalStem){
        $verbEndings = ['ar', 'ad', 'ade', 'es', 'it', 'er', 'aldes', 'at', 'ning', 'ningar', 'ande', 'andes', 'ningarna', 'as', 'lig', 'te', 'ades'];
        foreach($verbEndings as $ending){
            if(in_array($stem.$ending, $tokens)){
                if($tokens[$stem.$ending] === $stem.$ending){
                    $tokens[$stem.$ending] = $originalStem."a";
                    $this->foundWordForms++;
                }
            }
        }
        return $tokens;
    }

    function groupNounForms($stem, $tokens, $originalStem){
        if(mb_substr($stem, -1) === 'e' )
            $stem = mb_substr($stem, -1);
        $nounEndings = [ 'arna', 'erna', 'erne', 'issime', 'ernat', 'ers', 'ens', 'het', 'are', 'ess', 'en', 'een', 'et', 'ar', 'er', 'na', 't', 'ne', 'ars', 's', 'z'];


       /* foreach($nounEndings as $nounEnding){
            if (mb_substr($stem, -strlen($nounEnding)) === $nounEnding && in_array(mb_substr($stem, -strlen($nounEnding)), $tokens)){
                return $tokens;
            }
        }*/

        foreach($nounEndings as $ending){
            if(in_array($stem.$ending, $tokens)){
                if($tokens[$stem.$ending] === $stem.$ending){
                    $tokens[$stem.$ending] = $originalStem;
                    $this->foundWordForms++;
                }
            }
        }
        return $tokens;
    }

    function tokenize($string){
        $token_array = preg_split( "/[\s\r\f]+/", $string);
        return $token_array;
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
        //$this->logger->info('Searching for ' . count($allNames)." names among ".count($keys)." keys.");
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
                //$this->logger->info('Removing name: ' . $name." from array.");
                unset($keys[$search]);
            }
            $search = array_search($name.'s', $keys);
            if ($search !== false) {
                //$this->logger->info('Removing name: ' . $name."s from array.");
                unset($keys[$search]);
            }
        }
        if($searchKeys){
            $keys = array_flip($keys);

        }
        return $keys;
    }

    function loadStopwords($language){
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
        return $stopwords;
    }

    function strpos_all($haystack, $needle) {
        $offset = 0;
        $allpos = array();
        while (($pos = strpos($haystack, $needle, $offset)) !== FALSE) {
            $offset   = $pos + 1;
            $allpos[] = $pos;
        }
        return $allpos;
    }

}