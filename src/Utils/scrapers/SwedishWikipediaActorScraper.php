<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

namespace App\Utils\Scrapers;
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

use App\Utils\ScraperBase;

class WikipediaActorScraperBase extends ScraperBase
{

    protected $extractCrawler, $metaDataCrawler, $searchCrawler;
    protected $url, $client, $html, $em, $actor_repository, $wiki_title, $wikipedia_url, $actor;
    protected $search;
    protected $wikipedia_article;


    public function __construct($em)
    {
        $this->lang_array = ['Death date and age' => '[Dd]eath date and age',
            'Birth date' => '[Bb]irth date',
            'Death date' => '[Dd]eath date',
            'Birth place' => '[Bb]irth place',
            'Death place' => '[Dd]eath place'];

        $this->search = false;
        $this->wikipedia_article = false;
        $this->client = new Client();
        $this->em = $em;
        $this->actor_repository = $this->em->getRepository(Actor::class);
        $this->place_repository = $this->em->getRepository(Place::class);
    }

    public function connect($actor = null, $wiki_title = null, $lang = "sv")
    {
        if(is_null($wiki_title)) {
            if(!is_null($actor->getFirstName())&&!is_null($actor->getSurname())) {
                $actor_birth_year = $actor->getBirthdate()->format("Y");
                $actor_death_year = $actor->getDateOfDeath()->format("Y");
                $this->lang = $lang;
                $this->searchCrawler = $this->client->request('GET', "https://".$this->lang.".wikipedia.org/w/api.php?action=query&list=search&format=xml&srwhat=text&srsearch=" . $actor->getFirstName() . "%20" . $actor->getSurname()."%20".$actor_birth_year."%20".$actor_death_year);
                echo "<br>".$actor->getFirstName() . " " . $actor->getSurname()."<br>";
                //echo $this->searchCrawler->html();

                //$this->searchCrawler->filterXPath('//search/p')->each(function (Crawler $element, $i) {
                    //echo  $element->attr('snippet')."<br>";
                //});
                if($this->searchCrawler->filterXPath('//search/p[contains(@snippet,"'.$actor_birth_year.'")]')->count()){
                    $match = $this->searchCrawler->filterXPath('//search/p[contains(@snippet,"'.$actor_birth_year.'")]');
                    echo '//p[@snippet[text()[contains(.,"'.$actor_birth_year.'")]]'."<br>";
                    echo $match->html();
                    $pageId = $match->attr('pageid');
                    echo "Found by birth year: ". $match->attr('pageid')."<br>";
                }
                else{
                    echo "<br>".$actor_death_year;
                    if($this->searchCrawler->filterXPath('//search/p[contains(@snippet,"'.$actor_death_year.'")]')->count()){
                        $match = $this->searchCrawler->filterXPath('//search/p[contains(@snippet,"'.$actor_death_year.'")]');
                        echo '//search/p[contains(@snippet,"'.$actor_death_year.'")]'."<br>";
                        $pageId = $match->attr('pageid');
                        echo "Found by death year: ".$match->attr('pageid')."<br>";
                    }
                    else{
                        echo "Found no matches!";
                    }
                }
            }
            else{
                throw new Exception("Wikipedia id and actor names cannot all be null!");
                return false;
            }
            $this->searchCrawler = $this->client->request('GET','https://'.$this->lang.'wikipedia.org/w/api.php?action=query&prop=info&pageids='.$pageId.'&inprop=url');
            $wikiTitle = $this->searchCrawler->filterXPath('//'.$pageId.'/@title');
            $wikiTitle = str_replace(" ", "_", $pageTitle);
        }
        $this->metaDataCrawler = $this->client->request('GET', "https://".$this->lang.".wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&rvslots=main&format=xml&titles=" . $wiki_title);
        $this->extractCrawler = $this->client->request('GET', "https://".$this->lang.".wikipedia.org/w/api.php?action=query&prop=extracts&exintro=true&format=xml&titles=" . $wiki_title);
        $this->wiki_title = $wiki_title;
        $this->wikipedia_url = "https://".$this->lang.".wikipedia.org/wiki/" . $wiki_title;
        return true;
    }

    public function parse(): ?Actor
    {
        $this->debug = false;
        $failed = false;

        $this->actor = new Actor();

        try {
            $this->actor = $this->actor->setDescription($this->extractCrawler->filterXPath('//extract')->html());
        }
        catch (Exception $e){
            $failed = true;
        }

        try {
            $metadataString = html_entity_decode($this->metaDataCrawler->filterXPath('//slot')->html());
            $metadataString = mb_ereg_replace(" &ndash; ", " – ", $metadataString);
        }
        catch (Exception $e){
            if($failed) {
                return null;
            }
        }

        preg_match('/\| name = ([\w|\s]+)/', $metadataString, $name_string);

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

        $birthdate_array = []; $date_of_death_array = []; $birth_and_death_date_array = [];
        preg_match('/{{'.$this->lang_array['Death and date and age'].'\|(?P<death_year>\d+)\|(?P<death_month>\d+)\|(?P<death_day>\d+)\|(?P<birth_year>\d+)\|(?P<birth_month>\d+)\|(?P<birth_day>\d+)\|/', $metadataString, $birth_and_death_date_array);
        if(empty(array_filter($birth_and_death_date_array))){
            preg_match('/\((?P<birth_month>\w+) (?P<birth_day>\d+), (?P<birth_year>\d+), \[\[(?P<birth_place>[\wüåäö]+)]] – (?P<death_month>\w+) (?P<death_day>\d+), (?P<death_year>\d+), \[\[(?P<death_place>[\wåäöü]+)\]\]\)+/', $metadataString, $birth_and_death_date_array);
            if(!empty(array_filter($birth_and_death_date_array))) {
                $birth_place = $birth_and_death_date_array['birth_place'];
                $place_of_death = $birth_and_death_date_array['death_place'];
            }
        }

        if(!empty(array_filter($birth_and_death_date_array))) {

            $birthdate_array = ["year" => $birth_and_death_date_array['birth_year'],
                "month" => $birth_and_death_date_array['birth_month'],
                "day" => $birth_and_death_date_array['birth_day']
            ];
            $date_of_death_array = ["year" => $birth_and_death_date_array['death_year'],
                "month" => $birth_and_death_date_array['death_month'],
                "day" => $birth_and_death_date_array['death_day']
            ];
        }
        if(empty(array_filter($birthdate_array)))
            preg_match('/{{'.$this->lang_array['Birth date'].'\|df=yes\|(?P<year>\d+)\|(?P<month>\d+)\|(?P<day>\d+)}}/', $metadataString, $birthdate_array);


        if(!empty(array_filter($birthdate_array))) {
            if( preg_match('/\d+/', $birthdate_array['month'])){
                if (strlen($birthdate_array['month']) == 1) {
                    $birthdate_array['month'] = "0" . $birthdate_array['month'];
                }
                $text_birthdate = $birthdate_array['year'] . '-' . $birthdate_array['month'] . '-' . $birthdate_array['day'];
                $this->actor->setTextBirthdate($text_birthdate);
                $this->actor->setBirthdateByString($text_birthdate);
            }
            else{
                $date_string = $birthdate_array['day']." ".$birthdate_array['month']." ".$birthdate_array['year'];
                $this->actor->setBirthdateByString(DateTime::createFromFormat("d F Y", $date_string)->format('Y-m-d'));
            }
        }

        if(!empty(array_filter($date_of_death_array))) {
            if( preg_match('/\d+/', $date_of_death_array['month'])){
                if (strlen($date_of_death_array['month']) == 1) {
                    $date_of_death_array['month'] = "0" . $date_of_death_array['month'];
                }
                $text_date_of_death = $date_of_death_array['year'] . '-' . $date_of_death_array['month'] . '-' . $date_of_death_array['day'];
                $this->actor->setTextDateOfDeath($text_date_of_death);
                $this->actor->setDateOfDeathByString($text_date_of_death);
            }
            else{
                $date_string = $date_of_death_array['day']." ".$date_of_death_array['month']." ".$date_of_death_array['year'];
                $this->actor->setDateOfDeathByString(DateTime::createFromFormat("d F Y", $date_string)->format('Y-m-d'));
            }
        }

        if(!isset($birth_place)||$birth_place == ''){
            preg_match('/\|'.$this->lang_array['Birth place'].' = \[\[(\w+)\]\]/', $metadataString, $birthplace_array);
            if(!empty(array_filter($birthplace_array)))
                $birth_place = trim($birthplace_array[0]);
        }

        if(!isset($death_place)||$death_place == ''){
            preg_match('/\|'.$this->lang_array['Death place'].' = \[\[(\w+)\]\]/', $metadataString, $deathplace_array);
            if(!empty(array_filter($deathplace_array)))
                $death_place = trim($deathplace_array[0]);
        }

        if(isset($birth_place) && $birth_place !== ''){
            $birth_place_fields = ['name' => $birth_place];
            $birth_place_object = $this->place_repository->findOneBy($birth_place_fields);
            $this->actor->setBirthPlace($birth_place_object);
        }

        if(isset($death_place) && $death_place !== ''){
            $death_place_fields = ['name' => $death_place];
            $death_place_object = $this->place_repository->findOneBy($death_place_fields);
            $this->actor->setPlaceOfDeath($death_place_object);
        }

        return $this->actor;
    }
}
?>