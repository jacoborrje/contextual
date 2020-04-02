<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

namespace App\Utils\Scrapers;
use App\Utils\FileUploader;
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
use Doctrine\ORM\EntityManagerInterface;

use App\Utils\ScraperBase;
use App\Utils\Scrapers\AlvinPlaceScraper;
use App\Utils\Scrapers\SwedishWikipediaActorScraper;
use App\Utils\Scrapers\EnglishWikipediaActorScraper;
use App\Utils\Scrapers\BehindTheNameScraper;
use App\Utils\ActorService;

class AlvinActorScraper extends ScraperBase
{

    protected $url, $client, $crawler, $html, $em, $actor_repository, $alvin_volume, $alvin_id, $alvin_url;
    protected $alvin_volume_id = "863";
    protected $actor, $nameScraper, $swedishWikipediaScraper;
    protected $alt_surnames, $alt_first_names;
    protected $source;

    public function __construct(EntityManagerInterface $em, AlvinPlaceScraper $alvinPlaceScraper, ActorService $actorService, BehindTheNameScraper $nameScraper, SwedishWikipediaActorScraper $swedishWikipediaScraper)
    {
        $this->client = new Client();
        $this->em = $em;
        $this->actor_repository = $this->em->getRepository(Actor::class);
        $this->place_repository = $this->em->getRepository(Place::class);
        $this->alvin_volume = $this->em->getRepository(Volume::class)->find($this->alvin_volume_id);
        $this->alvinPlaceScraper = $alvinPlaceScraper;
        $this->actorService = $actorService;
        $this->nameScraper = $nameScraper;
        $this->swedishWikipediaScraper = $swedishWikipediaScraper;
    }

    public function connect($alvin_id)
    {
        $this->crawler =  $this->client->request('GET', "https://www.alvin-portal.org/alvin/view.jsf?pid=alvin-person:".$alvin_id);
        $this->alvin_id = $alvin_id;
        $this->alvin_url = "https://www.alvin-portal.org/alvin/view.jsf?pid=alvin-person:".$alvin_id;
        return true;
    }

    public function addSource(Source $source){
        $this->source = $source;
    }

    public function parse()
    {
        $this->debug = false;
        $this->parse_wikipedia = false;
        $this->actor = new Actor();

        $date_name_string = trim($this->crawler->filter('div.recordHeader')->children('h1.ltr')->text());

        $date_name_array = explode(",", $date_name_string);

        $this->actor->setFirstName(trim($date_name_array[1]));
        $this->actor->setSurname(trim($date_name_array[0]));

        echo "<br><br>" . $this->actor->getFirstName() . " " . $this->actor->getSurname() . "<br>";

        $this->alt_first_names = "";
        $this->alt_surnames = "";

        $alt_names = $this->crawler->filterXPath('//div[preceding-sibling::h2[1][text()[contains(.,"Alternative names")]]]');
        $alt_names->each(function (Crawler $element, $i) {
            $alt_name_array = explode(",", $element->text());
            if (count($alt_name_array)> 1) {
                if (strcmp(trim($alt_name_array[0]), $this->actor->getSurname()) !== 0) {
                    $this->alt_surnames .= trim($alt_name_array[0]) . ", ";
                    echo "Alt_surname: " . trim($alt_name_array[0]) . "<br>";
                }
                if (strcmp(trim(explode("(", trim($alt_name_array[1]))[0]), $this->actor->getFirstName()) !== 0) {
                    $this->alt_first_names .= trim(explode("(", trim($alt_name_array[1]))[0]) . ", ";
                    echo "Alt_first_name: " . trim(explode("(", trim($alt_name_array[1]))[0]) . "<br>";
                }
            }
        });
        $this->alt_first_names = substr($this->alt_first_names, 0, strlen($this->alt_first_names) - 2);
        $this->alt_surnames = substr($this->alt_surnames, 0, strlen($this->alt_surnames) - 2);

        $this->actor->setAltFirstNames($this->alt_first_names);
        $this->actor->setAltSurnames($this->alt_surnames);

        echo "Found " . count(array_filter(explode(", ", $this->actor->getAltFirstNames()))) . " alternative first names.<br>";
        echo "Found " . count(array_filter(explode(", ", $this->actor->getAltSurnames()))) . " alternative surnames.<br>";

        $text_birth_date_array = [];
        if (count($this->crawler->filterXPath('//h2[text()[contains(.,"Living period")]]/following-sibling::div'))) {
            $text_birth_date = $this->crawler->filterXPath('//h2[text()[contains(.,"Living period")]]/following-sibling::div')->text();
            $text_birth_date_array = explode(" ", $text_birth_date);
            $text_birth_date_array = array_filter($text_birth_date_array);
            $text_birth_date = $text_birth_date_array[1];
            $this->actor->setBirthdateByString($text_birth_date);
            echo "Setting birthdate to " . $text_birth_date . ".<br>";
        }

        $text_date_of_death_array = [];
        if (count($this->crawler->filterXPath('//h2[text()[contains(.,"Living period")]]/following-sibling::div/following-sibling::div'))) {
            $text_date_of_death = $this->crawler->filterXPath('//h2[text()[contains(.,"Living period")]]/following-sibling::div/following-sibling::div')->text();
            $text_date_of_death_array = explode(" ", $text_date_of_death);
            $text_date_of_death_array = array_filter($text_date_of_death_array);
            $text_date_of_death = $text_date_of_death_array[1];
            $this->actor->setDateOfDeathByString($text_date_of_death);
            echo "Setting date of death to " . $text_date_of_death . ".<br>";
        }

        $fields['first_name'] = $this->actor->getFirstName();
        $fields['surname'] = $this->actor->getSurname();

        $existing_actor = $this->actor_repository->findOneByAltSurnamesAndAltFirstNames($fields);

        if(!is_null($existing_actor)){
            echo "Actor name is: ".$this->actor."<br>";
            echo "Existing duplicate actor found: ". $existing_actor. "<br>";
            $this->actor = $this->actorService->merge($existing_actor,$this->actor);
            echo "New and old actor merged. Final actor name: ".$this->actor."<br>";
        }
        else if (!is_null($this->actor->getAltSurnames())&&$this->actor->getAltSurnames()!==""){
            $alt_surnames_array = explode(", ", $this->actor->getAltSurnames());
            foreach($alt_surnames_array as $alt_surname){
                $fields['first_name'] = $this->actor->getFirstName();
                $fields['surname'] = $alt_surname;
                $tentative_actor = $this->actor_repository->findOneByAltSurnamesAndAltFirstNames($fields);
                if(!is_null($tentative_actor))
                    $existing_actor = $tentative_actor;
                if(is_null($existing_actor)) {
                    if (!is_null($this->actor->getAltFirstNames() && $this->actor->getAltFirstNames() !== "")) {
                        $alt_first_names_array = explode(", ", $this->actor->getAltFirstNames());
                        foreach ($alt_first_names_array as $alt_first_name) {
                            $fields['first_name'] = $alt_first_name;
                            $tentative_actor = $this->actor_repository->findOneByAltSurnamesAndAltFirstNames($fields);
                            if(!is_null($tentative_actor))
                                $existing_actor = $tentative_actor;
                        }
                    }
                }
            }
        }
        else if (!is_null($this->actor->getAltFirstNames())&&$this->actor->getAltFirstNames()!==""){
            $alt_first_names_array = explode(", ", $this->actor->getAltFirstNames());
            foreach ($alt_first_names_array as $alt_first_name) {
                $fields['first_name'] = $alt_first_name;
                $fields['surname'] = $this->actor->getSurname();
                $tentative_actor = $this->actor_repository->findOneByAltSurnamesAndAltFirstNames($fields);
                if(!is_null($tentative_actor))
                    $existing_actor = $tentative_actor;
            }
        }
        if(is_null($existing_actor) && !is_null($this->source)){
            foreach($this->source->getActions() as $action){
                $correspondent = $action->getCorrespondent();
                if(!is_null($correspondent->getActor())){
                    $tentative_actor = $correspondent->getActor();
                    if($tentative_actor->getSurname() == $this->actor->getSurname() && $tentative_actor->getFirstName() === $this->actor->getFirstName()){
                        if($tentative_actor->getBirthDate() === $this->actor->getBirthdate() && $tentative_actor->getDateOfDeath() === $this->actor->getDateOfDeath()){
                            $existing_actor = $tentative_actor;
                        }
                    }
                }
            }
            foreach($this->source->getMentions() as $mention){
                if(!is_null($mention->getActor())){
                    $tentative_actor = $mention->getActor();
                    if($tentative_actor->getSurname() == $this->actor->getSurname() && $tentative_actor->getFirstName() === $this->actor->getFirstName()){
                        if($tentative_actor->getBirthDate() === $this->actor->getBirthdate() && $tentative_actor->getDateOfDeath() === $this->actor->getDateOfDeath()){
                            $existing_actor = $tentative_actor;
                        }
                    }
                }
            }
        }


        $gender_string = $this->crawler->filterXPath('//div[contains(@class, "recordIcon")]/following-sibling::div')->text();
        if(strcmp($gender_string, "male")===0||strcmp($gender_string, "Man")===0){
            $this->actor->setGender(0);
            echo "Actor is a man.<br>";
        }
        else if (strcmp($gender_string, "Kvinna")===0||strcmp($gender_string, "Woman")===0){
            $this->actor->setGender(1);
            echo "Actor is a woman.<br>";

        }

        if($this->crawler->filterXPath('//h2[text()[contains(.,"Living period")]]/following-sibling::div/a/@href')->count()){
            $birthPlaceHref = $this->crawler->filterXPath('//h2[text()[contains(.,"Living period")]]/following-sibling::div/a/@href');
            $birthPlaceId = explode("%3A", $birthPlaceHref->text())[1];
            echo "Parsing alvin birth place: ".$birthPlaceId."<br>";
            $this->alvinPlaceScraper->connect($birthPlaceId);
            $birthPlace = $this->alvinPlaceScraper->parse();
            $this->actor->setBirthPlace($birthPlace);
        }
        else{
            $this->parse_wikipedia = true;
        }

        if(count($text_birth_date_array) == 4) {
            $birth_place_string = trim($text_birth_date_array[3]);
            //echo $birth_place_string;
            if(count(explode(", ", $birth_place_string))>1) {
                $birthPlaceParent = explode(", ", $birth_place_string)[1];
            }
            $birth_place_string = explode(", ", $birth_place_string)[0];

            $birth_place_fields = ['name' => $birth_place_string];
            $birth_place = $this->place_repository->findOneBy($birth_place_fields);
            if(!is_null($birth_place)){
                $this->actor->setBirthPlace($birth_place);
            }
            else{

            }
        }
        else{
            $this->parse_wikipedia = true;
        }

        //TODO: rewrite this section to go through the place parser just like birthplace.

        if($this->crawler->filterXPath('//h2[text()[contains(.,"Living period")]]/following-sibling::div/following-sibling::div/a/@href')->count()){
            $deathPlaceHref = $this->crawler->filterXPath('//h2[text()[contains(.,"Living period")]]/following-sibling::div/following-sibling::div/a/@href');
            $deathPlaceId = explode("%3A", $deathPlaceHref->text())[1];
            echo "Parsing alvin place: ".$deathPlaceId."<br>";
            $this->alvinPlaceScraper->connect($deathPlaceId);
            $deathPlace = $this->alvinPlaceScraper->parse();
            $this->actor->setBirthPlace($deathPlace);
        }
        else{
            $this->parse_wikipedia = true;
        }

        if(count($text_date_of_death_array) == 4){
            $place_of_death_string = trim($text_date_of_death_array[3]);
            $place_of_death_fields = ['name' => $place_of_death_string];
            $place_of_death = $this->place_repository->findOneBy($place_of_death_fields);
            if(!is_null($place_of_death)){
                $this->actor->setPlaceOfDeath($place_of_death);
            }
        }

        $biography = $this->crawler->filterXPath('//text()[preceding-sibling::h2[1][text()[contains(.,"Biography")]]]');
        $this->biography_string = "";
        $biography->each(function (Crawler $element, $i) {
            $this->biography_string .= $element->text()."<br>";
        });

        $this->biography_string = urldecode($this->biography_string);

        $this->actor->setDescription($this->biography_string);

        $this->actor->setAlvinId($this->alvin_id);

        if($this->parse_wikipedia){
            $this->swedishWikipediaScraper;
            $connected = $this->swedishWikipediaScraper->connect($this->actor);
            if($connected)
                $new_actor = $this->swedishWikipediaScraper->parse();
            if(isset($new_actor)) {
                if(!is_null($new_actor)) {
                    $this->actor = $this->actorService->merge($this->actor, $new_actor);
                }
            }
        }

        if(!is_null($existing_actor)){
            echo "Actor name is: ".$this->actor."<br>";
            echo "Existing duplicate actor found: ". $existing_actor. "<br>";
            $this->actor = $this->actorService->merge($existing_actor,$this->actor);
            echo "New and old actor merged. Final actor name: ".$this->actor."<br>";
        }
        else{
            $this->actor->setCorrespondent(new Correspondent());
        }

        if(is_null($this->actor->getGender())){
            $firstName = explode(" ", $this->actor->getFirstName())[0];
            try {
                echo "Looking up gender at BehindTheName.<br>";
                $this->nameScraper->lookupName($firstName);
                $this->actor->setGender($this->nameScraper->getGender());
                if($this->actor->getGender()===0)
                    $gender = "masculine";
                else
                    $gender = "feminine";
                echo "Found out that ".$firstName." is a ".$gender." name.<br>";
            }
            catch(Exception $e){
                echo $e->getMessage();
            }
        }

        return $this->actor;
    }

    private function parseDate($title_field, $origin_field){
        $text_date = "";
        $found_date = false;
        $alt_months = [
            ['September' => ['9br']]
            ];

        if ((strpos($title_field, 'Letter') === 0)){
            $text_date = substr($title_field, 7, strlen($title_field));
            $text_date = trim(explode(',', $text_date)[0]);

            $myDateTime = DateTime::createFromFormat("j F Y", $text_date);
            if($myDateTime->format("Y-m-d")!==false) {
                $text_date = $myDateTime->format("Y-m-d");
                $found_date = true;
            }
        }
        return $text_date;
    }

    public function getAlvinId()
    {
        return $this->alvin_id;
    }

}