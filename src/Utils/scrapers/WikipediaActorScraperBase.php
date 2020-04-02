<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

namespace App\Utils\Scrapers;
use App\Utils\PlaceService;
use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Entity\Source;
use App\Entity\Action;
use App\Entity\Actor;
use App\Entity\Place;
use App\Entity\Correspondent;
use App\Entity\Mention;
use App\Entity\Volume;
use App\Entity\Institution;
use \DateTime;
use \Exception;
use \IntlDateFormatter;
use App\Utils\ScraperBase;
use App\Utils\GeographyHelper;
use App\Utils\FileUploader;

class WikipediaActorScraperBase extends ScraperBase
{

    protected $extractCrawler, $metaDataCrawler, $searchCrawler, $placeService;
    protected $url, $client, $html, $em, $actor_repository, $wiki_title, $wikipedia_url, $actor, $lang;
    protected $search;
    protected $wikipedia_article;
    protected $original_birth_year;
    protected $original_year_of_death;
    protected $combined_patterns, $singular_birthdate_patterns, $singular_death_date_patterns;
    protected $birthdate_array, $date_of_death_array, $birth_and_death_date_array;


    public function __construct($lang, $lang_array, $dateFormater, EntityManagerInterface $em, GeographyHelper $geographyHelper, PlaceService $placeService)
    {
        $this->lang_array = $lang_array;
        $this->lang = $lang;
        $this->search = false;
        $this->wikipedia_article = false;
        $this->client = new Client();
        $this->em = $em;
        $this->actor_repository = $this->em->getRepository(Actor::class);
        $this->place_repository = $this->em->getRepository(Place::class);
        $this->GeographyHelper = $geographyHelper;
        $this->dateFormater = $dateFormater;
        $this->placeService = $placeService;
    }

    public function connect($actor = null, $wiki_title = null)
    {
        $wikiTitle = "";
        if(!is_null($actor))
            $this->actor = $actor;
        else
            $this->actor = new Actor();

        if(is_null($wiki_title)) {
            echo "Parsing ".$this->lang.".wikipedia.org<br>";
            if(!is_null($actor->getFirstName())&&!is_null($actor->getSurname())) {
                $wikiTitle = null;

                $tentative_wiki_title = $this->actor->getFirstName()."_".$this->actor->getSurname();
                $tentative_article = $this->client->request('GET', "https://".$this->lang.".wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&rvslots=main&format=xml&titles=".$tentative_wiki_title);

                if($tentative_article->filterXPath("//slots/slot")->count()===1){
                    $tentative_text = $tentative_article->text();
                    if (preg_match("/#redirect \[\[(?P<redirect_title>[\w\såäöÅÄÖüÜéÉèÈ]+)]]/", $tentative_text)){
                        echo "Redirect found in article.<br>";
                        preg_match("/#redirect \[\[(?P<redirect_title>[\w\såäöÅÄÖüÜéÉèÈ]+)]]/", $tentative_text, $redirect_title);
                        $tentative_wiki_title = str_replace(" ", "_", $redirect_title['redirect_title']);
                    }
                    $wikiTitle = $tentative_wiki_title;
                }
                if(is_null($wikiTitle)) {
                    if (!is_null($actor->getAltSurnames())) {
                        echo "Alternative surnames: ". $actor->getAltSurnames()."<br>";
                        $alt_surnames_array = array_filter(explode(", ", $actor->getAltSurnames()));
                        echo "Number of surnames: ".count($alt_surnames_array)."<br>";
                        $alt_first_names_array = array_filter(explode(", ", $actor->getAltFirstNames()));
                        if (count($alt_surnames_array) > 0) {
                            foreach ($alt_surnames_array as $alt_surname) {
                                $tentative_wiki_title = $this->actor->getFirstName() . "_" . $alt_surname;
                                $tentative_article = $this->client->request('GET', "https://" . $this->lang . ".wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&rvslots=main&format=xml&titles=" . $tentative_wiki_title);
                                $tentative_text = $tentative_article->text();
                                if ($tentative_article->filterXPath("//slots/slot")->count() === 1) {
                                    if (preg_match("/#redirect \[\[(?P<redirect_title>[\w\såäöÅÄÖüÜéÉèÈ]+)]]/", $tentative_text)) {
                                        echo "Redirect found in article.<br>";
                                        preg_match("/#redirect \[\[(?P<redirect_title>[\w\såäöÅÄÖüÜéÉèÈ]+)]]/", $tentative_text, $redirect_title);
                                        $tentative_wiki_title = str_replace(" ", "_", $redirect_title['redirect_title']);
                                    }
                                    $wikiTitle = $tentative_wiki_title;
                                } else if (count($alt_first_names_array)>0){
                                    foreach ($alt_first_names_array as $alt_first_name) {
                                        $tentative_wiki_title = $alt_first_name . "_" . $alt_surname;
                                        $tentative_article = $this->client->request('GET', "https://" . $this->lang . ".wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&rvslots=main&format=xml&titles=" . $tentative_wiki_title);
                                        $tentative_text = $tentative_article->text();
                                        if ($tentative_article->filterXPath("//slots/slot")->count() === 1) {
                                            if (preg_match("/#redirect \[\[(?P<redirect_title>[\w\såäöÅÄÖüÜéÉèÈ]+)]]/", $tentative_text)) {
                                                echo "Redirect found in article.<br>";
                                                preg_match("/#redirect \[\[(?P<redirect_title>[\w\såäöÅÄÖüÜéÉèÈ]+)]]/", $tentative_text, $redirect_title);
                                                $tentative_wiki_title = str_replace(" ", "_", $redirect_title['redirect_title']);
                                            }
                                            $wikiTitle = $tentative_wiki_title;
                                        }
                                    }
                                }
                            }
                        }
                        else if(count($alt_first_names_array)>0){
                            foreach ($alt_first_names_array as $alt_first_name) {
                                $tentative_wiki_title = $alt_first_name . "_" . $actor->getSurname();
                                $tentative_article = $this->client->request('GET', "https://" . $this->lang . ".wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&rvslots=main&format=xml&titles=" . $tentative_wiki_title);
                                $tentative_text = $tentative_article->text();
                                if ($tentative_article->filterXPath("//slots/slot")->count() === 1) {
                                    if (preg_match("/#redirect \[\[(?P<redirect_title>[\w\såäöÅÄÖüÜéÉèÈ]+)]]/", $tentative_text)) {
                                        echo "Redirect found in article.<br>";
                                        preg_match("/#redirect \[\[(?P<redirect_title>[\w\såäöÅÄÖüÜéÉèÈ]+)]]/", $tentative_text, $redirect_title);
                                        $tentative_wiki_title = str_replace(" ", "_", $redirect_title['redirect_title']);
                                    }
                                    $wikiTitle = $tentative_wiki_title;
                                }
                            }
                        }
                    }
                }
                if(is_null($wikiTitle)&&!is_null($actor->getBirthdate())&&!is_null($actor->getDateOfDeath())){
                    $actor_birth_year = $actor->getBirthdate()->format("Y");
                    $this->original_birth_year = $actor_birth_year;
                    $actor_death_year = $actor->getDateOfDeath()->format("Y");
                    $this->original_year_of_death = $actor_death_year;

                    $search_url = "https://" . $this->lang . ".wikipedia.org/w/api.php?action=query&list=search&format=xml&srwhat=text&srsearch=" . $this->actor->getFirstName() . "%20" . $this->actor->getSurname() . "%20" . $actor_birth_year . "%20" . $actor_death_year;
                    echo "Searching wikipedia for matching title: " . $search_url . "<br>";
                    $this->searchCrawler = $this->client->request('GET', $search_url);
                    if ($this->searchCrawler->filterXPath('//search/p[contains(@snippet,"' . $actor_birth_year . '")]')->count()) {
                        $match = $this->searchCrawler->filterXPath('//search/p[contains(@snippet,"' . $actor_birth_year . '")]');
                        echo 'Searching extracts for //p[@snippet[text()[contains(.,"' . $actor_birth_year . '")]]' . "<br>";
                        $pageId = $match->attr('pageid');
                        echo "Found by birth year: " . $match->attr('pageid') . "<br>";
                    } else if ($this->searchCrawler->filterXPath('//search/p[contains(@snippet,"' . $actor_death_year . '")]')->count()) {
                        $match = $this->searchCrawler->filterXPath('//search/p[contains(@snippet,"' . $actor_death_year . '")]');
                        echo '//search/p[contains(@snippet,"' . $actor_death_year . '")]' . "<br>";
                        $pageId = $match->attr('pageid');
                        echo "Found by death year: " . $match->attr('pageid') . "<br>";
                    }
                    if (isset($pageId)) {
                        $this->searchCrawler = $this->client->request('GET', 'https://' . $this->lang . '.wikipedia.org/w/api.php?action=query&format=xml&prop=info&pageids=' . $pageId . '&inprop=url');
                        $wikiTitle = $this->searchCrawler->filterXPath('//page/@canonicalurl')->text();
                        $wikiTitle = explode("wiki/", $wikiTitle)[1];
                    } else {
                        echo "Found no wikipedia page id. Exiting.<br>";
                        return false;
                    }
                }
            }
            else{
                echo "No actor!";
                return false;
            }
        }
        else{
            $wikiTitle = $wiki_title;
        }

        echo "Wikipedia title is: " . $wikiTitle . "<br>";
        $this->metaDataCrawler = $this->client->request('GET', "https://".$this->lang.".wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&rvslots=main&format=xml&titles=" . $wikiTitle);
        echo "Meta URL: "."https://".$this->lang.".wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&rvslots=main&format=xml&titles=" . $wikiTitle ."<br>";
        $this->extractCrawler = $this->client->request('GET', "https://".$this->lang.".wikipedia.org/w/api.php?action=query&prop=extracts&exintro=true&format=xml&titles=" . $wikiTitle);
        echo "Extract URL: ". "https://".$this->lang.".wikipedia.org/w/api.php?action=query&prop=extracts&exintro=true&format=xml&titles=" . $wikiTitle."<br>";
        $this->wiki_title = $wikiTitle;
        $this->wikipedia_url = "https://".$this->lang.".wikipedia.org/wiki/" . $wiki_title;
        return true;
    }

    public function parse(): ?Actor
    {
        $this->debug = false;
        $failed = false;

        try {
            $this->actor->setDescription(html_entity_decode($this->extractCrawler->filterXPath('//page/extract')->html()));
        }
        catch (Exception $e){
            $failed = true;
        }

        try {
            $metadataString = html_entity_decode($this->metaDataCrawler->filterXPath('//rev/slots/slot')->html());
            $metadataString = mb_ereg_replace(" &ndash; ", " – ", $metadataString);
        }
        catch (Exception $e){
            if($failed) {
                return null;
            }
        }

        preg_match('/\| name = ([\w|\s]+)/', $metadataString, $name_string);

        //Get the actor name.
        if(!empty(array_filter($name_string))){
            $name_array = explode(" ", trim($name_string[1]));
            $surname = end($name_array);
            $first_name = "";
            for($i=0;$i<count($name_array)-1;$i++){
                $first_name .= $name_array[$i] . " ";
            }
            $first_name = trim($first_name);

            $this->actor->setSurname($surname);
            $this->actor->setFirstName($first_name);
        }

        //Get the actor gender
        if(is_null($this->actor->getGender())) {
            if (strpos($metadataString, '[[' . $this->lang_array['category'] . ':' . $this->lang_array['men'] . "]]") > 0) {
                $this->actor->setGender(0);
                echo "Actor is a man.<br>";
            } else if (strpos($metadataString, '[[' . $this->lang_array['category'] . ':' . $this->lang_array['women'] . "]]") > 0) {
                $this->actor->setGender(1);
                echo "Actor is a woman.<br>";
            }
        }

        $this->birthdate_array = []; $this->date_of_death_array = []; $this->birth_and_death_date_array = [];

        //Combined searches for birth and death dates
        if($this->actor->getBirthdateAccuracy()<2 || $this->actor->getDateOfDeathAccuracy()<2 || is_null($this->actor->getBirthPlace()) || is_null($this->actor->getPlaceOfDeath())) {
            $combined_patterns = $this->combined_patterns;
            for ($i = 0; (empty(array_filter($this->birth_and_death_date_array)) || !isset($death_place) || !isset($birth_place)) && $i < count($combined_patterns); $i++) {
                $pattern = $combined_patterns[$i];
                preg_match($pattern, $metadataString, $birth_and_death_date_array_temp);
                if (!empty(array_filter($birth_and_death_date_array_temp))) {
                    if (array_key_exists('birth_place', $birth_and_death_date_array_temp))
                        if ($birth_and_death_date_array_temp['birth_place'] !== '')
                            $birth_place = $birth_and_death_date_array_temp['birth_place'];
                    if (array_key_exists('death_place', $birth_and_death_date_array_temp))
                        if ($birth_and_death_date_array_temp['death_place'] !== '')
                            $death_place = $birth_and_death_date_array_temp['death_place'];

                    echo "Found match for birth and death dates ";
                    echo "using pattern: '" . $pattern . "':<br>";
                    print_r($birth_and_death_date_array_temp);
                    if(empty($this->birth_and_death_date_array)) {
                        $this->birth_and_death_date_array = $birth_and_death_date_array_temp;
                        echo "<br>";
                        $this->birthdate_array = ["year" => $this->birth_and_death_date_array['birth_year'],
                            "month" => $this->birth_and_death_date_array['birth_month'],
                            "day" => $this->birth_and_death_date_array['birth_day']
                        ];
                        $this->date_of_death_array = ["year" => $this->birth_and_death_date_array['death_year'],
                            "month" => $this->birth_and_death_date_array['death_month'],
                            "day" => $this->birth_and_death_date_array['death_day']
                        ];
                    }
                }
            }
            if (empty(array_filter($this->birth_and_death_date_array))) {
                echo "Found no combined birth and death date match. <br>";
            }
        }

        //Singular searches for birthdate
        if($this->actor->getBirthdateAccuracy()<2||is_null($this->actor->getBirthPlace())) {
            $singular_birthdate_patterns = $this->singular_birthdate_patterns;
            for ($i = 0; (empty(array_filter($this->birthdate_array)) || !isset($birth_place)) && $i < count($singular_birthdate_patterns); $i++) {
                $pattern = $singular_birthdate_patterns[$i];
                preg_match($pattern, $metadataString, $birthdate_array_temp);
                if (!empty(array_filter($birthdate_array_temp))) {
                    if (array_key_exists('birth_place', $birthdate_array_temp)) {
                        if ($birthdate_array_temp['birth_place'] !== '') {
                            echo "Found birth place: " . trim($birthdate_array_temp['birth_place']) . "<br>";
                            $birth_place = trim($birthdate_array_temp['birth_place']);
                            $birth_place = str_replace("byn ", "", $birth_place);
                            $birth_place = str_replace("staden ", "", $birth_place);
                        }
                    }
                    echo "Found match for birthdate ";
                    echo "using pattern: '" . $pattern . "':<br>";
                    print_r($this->birthdate_array);
                    echo "<br>";
                    if(empty($this->birthdate_array))
                        $this->birthdate_array = $birthdate_array_temp;
                }
            }
            if (empty(array_filter($this->birthdate_array))) {
                echo "Found no singular birthdate match. <br>";
            }
        }

        if($this->actor->getDateOfDeathAccuracy()<2 || is_null($this->actor->getPlaceOfDeath())) {
            echo "Looking for date and place of death.<br>";
            //Singular searches for death dates
            $singular_death_date_patterns = $this->singular_death_date_patterns;
            for ($i = 0; (empty(array_filter($this->date_of_death_array)) || !isset($death_place)) && $i < count($singular_death_date_patterns); $i++) {
                $pattern = $singular_death_date_patterns[$i];
                preg_match($pattern, $metadataString, $date_of_death_array_temp);
                if (!empty(array_filter($date_of_death_array_temp))) {
                    echo "Match found.<br>";
                    if (array_key_exists('death_place', $date_of_death_array_temp)) {
                        if ($date_of_death_array_temp['death_place'] !== '') {
                            echo "Found place of death: " . trim($date_of_death_array_temp['death_place']) . "<br>";
                            if(isset($date_of_death_array_temp['death_place_parent'])) {
                                if ($date_of_death_array_temp['death_place_parent'] !== '') {
                                    $death_place = trim($date_of_death_array_temp['death_place_parent']);
                                }
                                else{
                                    $death_place = trim($date_of_death_array_temp['death_place']);
                                }
                            }
                            else{
                                $death_place = trim($date_of_death_array_temp['death_place']);
                            }
                            $death_place = str_replace("byn ", "", $death_place);
                            $death_place = str_replace("staden ", "", $death_place);
                        }
                    }
                    if(empty($this->date_of_death_array)){
                        $this->date_of_death_array = $date_of_death_array_temp;
                    }
                    echo "Found match for date of death ";
                    echo "using pattern: '" . $pattern . "':<br>";
                    print_r($this->date_of_death_array);
                    echo "<br>";
                }
            }
            if (empty(array_filter($this->date_of_death_array))) {
                echo "Found no singular date of death match. <br>";
            }
        }

        //Setting correct birth date values
        if($this->actor->getBirthdateAccuracy()<2) {
            if (!empty(array_filter($this->birthdate_array))) {
                if (array_key_exists('month', $this->birthdate_array) && array_key_exists('day', $this->birthdate_array)) {
                    if ($this->birthdate_array['month'] !== '' && $this->birthdate_array['day'] !== '') {
                        if (!preg_match('/\d+/', $this->birthdate_array['month'])) {
                            if (strlen(trim($this->birthdate_array['month'])) === 3)
                                $this->dateFormater->setPattern('MMM');
                            else
                                $this->dateFormater->setPattern('MMMM');
                            $this->dateFormater->parse($this->birthdate_array['month']);
                            $this->dateFormater->setPattern('MM');
                            $this->birthdate_array['month'] = $this->dateFormater->format(0);
                        }
                        if (strlen($this->birthdate_array['month']) == 1) {
                            $this->birthdate_array['month'] = "0" . $this->birthdate_array['month'];
                        }
                        $text_birthdate = $this->birthdate_array['year'] . '-' . $this->birthdate_array['month'] . '-' . $this->birthdate_array['day'];
                        if (isset($this->original_birth_year)) {
                            if ($this->birthdate_array['year'] === $this->original_birth_year) {
                                $this->actor->setTextBirthdate($text_birthdate);
                                $this->actor->setBirthdateByString($text_birthdate);
                            } else {
                                echo "Article birth year does not match actor birth year. Probably wrong article. Exiting.<br>";
                                return null;
                            }
                        } else {
                            $this->actor->setTextBirthdate($text_birthdate);
                            $this->actor->setBirthdateByString($text_birthdate);
                        }
                    }
                } else if (array_key_exists('year', $this->birthdate_array) && $this->birthdate_array['year'] !== '') {
                    $text_birthdate = $this->birthdate_array['year'];
                    if (isset($this->original_birth_year)) {
                        if ($this->birthdate_array['year'] === $this->original_birth_year) {
                            $this->actor->setTextBirthdate($text_birthdate);
                            $this->actor->setBirthdateByString($text_birthdate);
                        } else {
                            echo "Article birth year does not match actor birth year. Probably wrong article. Exiting.<br>";
                            return null;
                        }
                    } else {
                        $this->actor->setTextBirthdate($text_birthdate);
                        $this->actor->setBirthdateByString($text_birthdate);
                    }
                }
            }
        }

        //Setting correct death date values
        if($this->actor->getDateOfDeathAccuracy()<2) {
            if (!empty(array_filter($this->date_of_death_array))) {
                if (array_key_exists('month', $this->date_of_death_array) && array_key_exists('day', $this->date_of_death_array)) {
                    if ($this->date_of_death_array['month'] !== '' && $this->date_of_death_array['day'] !== '') {
                        if (!preg_match('/\d+/', $this->date_of_death_array['month'])) {
                            if (strlen(trim($this->date_of_death_array['month'])) === 3)
                                $this->dateFormater->setPattern('MMM');
                            else
                                $this->dateFormater->setPattern('MMMM');
                            $this->dateFormater->parse($this->date_of_death_array['month']);
                            $this->dateFormater->setPattern('MM');
                            $this->date_of_death_array['month'] = $this->dateFormater->format(0);
                        }
                        if (strlen($this->date_of_death_array['month']) == 1) {
                            $this->date_of_death_array['month'] = "0" . $this->date_of_death_array['month'];
                        }
                        $text_date_of_death = $this->date_of_death_array['year'] . '-' . $this->date_of_death_array['month'] . '-' . $this->date_of_death_array['day'];
                        if (isset($this->original_year_of_death)) {
                            if ($this->date_of_death_array['year'] === $this->original_year_of_death) {
                                $this->actor->setTextDateOfDeath($text_date_of_death);
                                $this->actor->setDateOfDeathByString($text_date_of_death);
                            } else {
                                echo "Article death year does not match actor death year. Probably wrong article. Exiting.<br>";
                                return null;
                            }
                        } else {
                            $this->actor->setTextDateOfDeath($text_date_of_death);
                            $this->actor->setDateOfDeathByString($text_date_of_death);
                        }
                    }
                } else if (array_key_exists('year', $this->date_of_death_array)) {
                    if ($this->date_of_death_array['year'] !== '') {
                        $text_date_of_death = $this->date_of_death_array['year'];
                        if (isset($this->original_year_of_death)) {
                            if ($this->birthdate_array['year'] === $this->original_year_of_death) {
                                $this->actor->setTextDateOfDeath($text_date_of_death);
                                $this->actor->setDateOfDeathByString($text_date_of_death);
                            } else {
                                echo "Article death year does not match actor death year. Probably wrong article. Exiting.<br>";
                                return null;
                            }
                        } else {
                            $this->actor->setTextDateOfDeath($text_date_of_death);
                            $this->actor->setDateOfDeathByString($text_date_of_death);
                        }
                    }
                }
            }
        }


        //TODO: Move the expressions for places out of the base controller.
        if(is_null($this->actor->getBirthplace())) {
            //Searching for singular birth place
            if (!isset($birth_place) || $birth_place == '') {
                echo "Searching for: " . '/\|' . $this->lang_array['Birth place'] . '[\s]+=[\s]+\[\[([\wåäöü]+)\]\]/' . '<br>';
                preg_match('/\|' . $this->lang_array['Birth place'] . '[\s]+=[\s]+\[\[(?P<place>[\wåäöü]+)\]\]/', $metadataString, $birthplace_array);
                if (!empty(array_filter($birthplace_array))) {
                    $birth_place = trim($birthplace_array['place']);
                    echo "Found match: ";
                    print_r($birth_place);
                    echo "<br>";
                } else {
                    echo "Found no match.<br>";
                }
            }
            // Setting birth place
            if(isset($birth_place)){
                if($birth_place !== '') {
                    $birth_place_fields = ['name' => $birth_place];
                    $birth_place_object = $this->place_repository->findOneByNameAndAltNames($birth_place_fields);
                    if(!is_null($birth_place_object)){
                        echo "Found ".$birth_place_object." in the database.<br>";
                    }
                    if (is_null($birth_place_object) || is_null($birth_place_object->getType())) {
                        echo "Looking up ".$birth_place_object."<br>";
                        $new_birth_place_object = $this->GeographyHelper->placeFromAddress($birth_place);
                        if(!is_null($birth_place_object)){
                            $birth_place_object = $this->placeService->merge($birth_place_object, $new_birth_place_object);
                        }
                    }
                    if (!is_null($birth_place_object)) {
                        $this->actor->setBirthPlace($birth_place_object);
                    }
                }
            }
        }

        if(is_null($this->actor->getPlaceOfDeath())) {
            //Searching for singular death place
            if (!isset($death_place) || $death_place == '') {
                echo "Searching for: " . '/\|' . $this->lang_array['Death place'] . '[\s]+=[\s]+\[\[([\wåäöü]+)\]\]/' . "<br>";
                preg_match('/\|' . $this->lang_array['Death place'] . '[\s]+=[\s]+\[\[(?P<place>[\wåäöü]+)\]\]/', $metadataString, $deathplace_array);
                if (!empty(array_filter($deathplace_array))) {
                    $death_place = trim($deathplace_array['place']);
                    echo "Found match: ";
                    print_r($death_place);
                    echo "<br>";
                } else {
                    echo "Found no match.<br>";
                }
            }

            // Setting place of death
            if(isset($death_place)){
                if($death_place !== '') {
                    $death_place_fields = ['name' => $death_place];
                    $death_place_object = $this->place_repository->findOneByNameAndAltNames($death_place_fields);
                    if(!is_null($death_place_object)){
                        echo "Found ".$death_place_object." in the database.<br>";
                    }
                    if (is_null($death_place_object)||is_null($death_place_object->getType())) {
                        echo "Looking up ".$death_place_object."<br>";
                        $new_death_place_object = $this->GeographyHelper->placeFromAddress($death_place);
                        if(!is_null($death_place_object)){
                            $death_place_object = $this->placeService->merge($death_place_object, $new_death_place_object);
                        }
                    }
                    if (!is_null($death_place_object)) {
                        $this->actor->setPlaceOfDeath($death_place_object);
                    }
                }
            }
        }
        return $this->actor;
    }
}
?>