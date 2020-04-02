<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

namespace App\Utils\Scrapers;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Mapping\Driver\File;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Utils\FileUploader;
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
use App\Utils\GeographyHelper;
use App\Utils\PlaceService;


class AlvinPlaceScraper extends ScraperBase
{

    protected $url, $client, $crawler, $html, $em, $actor_repository, $alvin_volume, $alvin_id, $alvin_url;
    protected $place;

    public function __construct(EntityManagerInterface $em, GeographyHelper $geographyHelper, PlaceService $placeService)
    {
        $this->client = new Client();
        $this->em = $em;
        $this->actor_repository = $this->em->getRepository(Actor::class);
        $this->place_repository = $this->em->getRepository(Place::class);
        $this->geography_helper = $geographyHelper;
        $this->placeService = $placeService;
    }

    public function connect($alvin_id)
    {
        $this->crawler =  $this->client->request('GET', "https://www.alvin-portal.org/alvin/view.jsf?pid=alvin-place:".$alvin_id);
        $this->alvin_id = $alvin_id;
        $this->alvin_url = "https://www.alvin-portal.org/alvin/view.jsf?pid=alvin-place:".$alvin_id;
        return true;
    }

    public function parse()
    {
        //TODO: Rewrite the parse method to fetch places from Alvin.

        $this->debug = false;
        $this->place = new Place();

        $full_name_string = trim($this->crawler->filter('div.recordHeader')->children('h1.ltr')->text());

        echo "The full name of the place is ". $full_name_string."<br>";

        $name_array = explode (", ", $full_name_string);
        $name_string = trim($name_array[0]);
        if(count($name_array)>1) {
            $parent_string = trim($name_array[1]);
        }

        echo "The name of the place is: ".$name_string."<br>";
        $this->place->setName($name_string);

        $existing_place = $this->place_repository->findOneByNameAndAltNames(['name' => $name_string]);
        if(!is_null($existing_place)){
            echo "Existing place found: ".$existing_place->getName();
            $this->place = $existing_place;
        }

        $this->alt_names = "";
        $alt_names_container = $this->crawler->filterXPath('//h2[text()[contains(.,"Alternative names")]]/following-sibling::div/div/ul/li');
        $alt_names_container->each(function (Crawler $element, $i) {
            $alt_name_array = explode(" (", $element->text());
            $this->alt_names .= trim($alt_name_array[0]). ", ";
        });
        $this->place->setAltNames(substr($this->alt_names, 0, strlen($this->alt_names)-2));

        if(isset($parent_string)) {
            echo "The name of the parent is: " . $parent_string . "<br>";
            $parent = $this->place_repository->findBy(['name' => $parent_string]);
            if(!is_null($parent)){
                echo "Found a parent in the database!";
            }
            else
                echo "Did not find a parent in the database.<br>";
        }

        //Get the parent country.
        if(isset($parent)) {
            try {
                $country = trim($this->crawler->filterXPath('//text()[preceding-sibling::h2[1][text()[contains(.,"Country")]]]')->text());
                $parent = $this->place_repository->findOneByCountriesAndName(['name' => $country]);


                if (is_null($parent)) {
                    $parent_location = $this->geography_helper->geocodeCountry($country);
                    if (!is_null($parent_location)) {
                        $parent = new Place();
                        $parent->setName($country);
                        $parent->setLng($parent_location['lon']);
                        $parent->setLat($parent_location['lat']);
                        $parent->setType(1);
                    }
                }
                $this->place->setParent($parent);
            } catch (Exception $e) {

            }
        }

        try {
            $lat = trim($this->crawler->filterXPath('//text()[preceding-sibling::h2[1][text()[contains(.,"Latitude")]]]')->text());
            $this->place->setLat($lat);
        }
        catch (Exception $e){

        }
        try {
            $lon = trim($this->crawler->filterXPath('//text()[preceding-sibling::h2[1][text()[contains(.,"Longitude")]]]')->text());
            $this->place->setLng($lon);
        }
        catch (Exception $e){

        }

        if(!isset($lat)||!isset($lon)){
            if(isset($parent_string))
                $location = $this->geography_helper->geocodeNameAndParent($name_string, $parent_string);
            else
                $location = $this->geography_helper->geocodeAddress($name_string);
            if(!is_null($location)){
                $lat = $location['lat'];
                $this->place->setLat($location['lat']);
                $lon = $location['lng'];
                $this->place->setLng($location['lng']);
            }
        }

        $this->place->setAlvinId($this->alvin_id);

        //Find out what type the place is.
        if(isset($lon) && isset($lat) && is_null($this->place->getType())) {
            echo "Checking what place this is.<br>";
            $type = $this->geography_helper->getPlaceType($this->place->getName(), $this->place->getLng(), $this->place->getLat());
            echo "Setting type to ".$type."<br>";
            $this->place->setType($type);
        }

        return $this->place;
    }
}