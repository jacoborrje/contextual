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

class AlvinActorScraper extends ScraperBase
{

    protected $url, $client, $crawler, $html, $em, $actor_repository, $alvin_volume, $alvin_id, $alvin_url;
    protected $alvin_volume_id = "863";
    protected $actor;

    public function __construct($em)
    {
        $this->client = new Client();
        $this->em = $em;
        $this->actor_repository = $this->em->getRepository(Actor::class);
        $this->place_repository = $this->em->getRepository(Place::class);
        $this->alvin_volume = $this->em->getRepository(Volume::class)->find($this->alvin_volume_id);
    }

    public function connect($alvin_id)
    {
        $this->crawler =  $this->client->request('GET', "https://www.alvin-portal.org/alvin/view.jsf?pid=alvin-person:".$alvin_id);
        $this->alvin_id = $alvin_id;
        $this->alvin_url = "https://www.alvin-portal.org/alvin/view.jsf?pid=alvin-person:".$alvin_id;
        return true;
    }

    public function parse()
    {
        $this->debug = false;
        $this->actor = new Actor();

        //TODO: Check if the actor already exists in the database, in that case merge the new information with the old

        $date_name_string = trim($this->crawler->filter('div.recordHeader')->children('h1.ltr')->text());

        $date_name_array = explode(",", $date_name_string);
        $this->actor->setFirstName($date_name_array[1]);
        $this->actor->setSurname($date_name_array[0]);

        $gender_string = $this->crawler->filterXPath('//div[contains(@class, "recordIcon")]/following-sibling::div')->text();

        //echo $gender_string;

        if(strcmp($gender_string, "male")===0){
            $this->actor->setGender(0);
        }
        else{
            $this->actor->setGender(1);
        }

        $text_birth_date = $this->crawler->filterXPath('//h2[text()[contains(.,"Living period")]]/following-sibling::div')->text();
        $text_birth_date_array = explode(" ", $text_birth_date);
        $text_birth_date = $text_birth_date_array[1];
        $this->actor->setBirthdateByString($text_birth_date);

        $text_date_of_death = $this->crawler->filterXPath('//h2[text()[contains(.,"Living period")]]/following-sibling::div/following-sibling::div')->text();
        $text_date_of_death_array = explode(" ", $text_date_of_death);
        $text_date_of_death = $text_date_of_death_array[1];
        $this->actor->setDateOfDeathByString($text_date_of_death);

        $text_birth_date_array = array_filter($text_birth_date_array);
        //print_r($text_birth_date_array);
        if(count($text_birth_date_array) == 4) {
            $birth_place_string = trim($text_birth_date_array[3]);
            //echo $birth_place_string;
            $birth_place_fields = ['name' => $birth_place_string];
            $birth_place = $this->place_repository->findOneBy($birth_place_fields);
            if(!is_null($birth_place)){
                $this->actor->setBirthPlace($birth_place);
            }
        }

        $text_date_of_death_array = array_filter($text_date_of_death_array);
        if(count($text_date_of_death_array) == 4){
            $place_of_death_string = trim($text_date_of_death_array[3]);
            $place_of_death_fields = ['name' => $place_of_death_string];
            $place_of_death = $this->place_repository->findOneBy($place_of_death_fields);
            if(!is_null($place_of_death)){
                $this->actor->setPlaceOfDeath($place_of_death);
            }
        }

        $this->alt_first_names = "";
        $this->alt_surnames = "";

        $alt_names = $this->crawler->filterXPath('//div[preceding-sibling::h2[1][text()[contains(.,"Alternative names")]]]');
        $alt_names->each(function (Crawler $element, $i) {
            $alt_name_array = explode(",", $element->text());
            $this->alt_surnames .= $alt_name_array[0]. ", ";
            $this->alt_first_names .= trim(explode("(", $alt_name_array[1])[0]).", ";
        });
        $this->alt_first_names = substr($this->alt_first_names, 0, strlen($this->alt_first_names)-2);
        $this->alt_surnames = substr($this->alt_surnames, 0, strlen($this->alt_surnames)-2);

        $this->actor->setAltFirstNames($this->alt_first_names);
        $this->actor->setAltSurnames($this->alt_surnames);

        $biography = $this->crawler->filterXPath('//text()[preceding-sibling::h2[1][text()[contains(.,"Biography")]]]');
        $this->biography_string = "";
        $biography->each(function (Crawler $element, $i) {
            $this->biography_string .= $element->text()."<br>";
        });

        $this->actor->setDescription($this->biography_string);

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