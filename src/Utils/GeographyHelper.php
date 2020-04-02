<?php
namespace App\Utils;

use \App\Entity\Place;
use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client;
use \Exception;


class GeographyHelper
{
    protected $client, $geonamesCrawler, $em;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->placeRepository = $this->em->getRepository(Place::class);
        $this->client = new Client();

    }

    function geoDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    function sortResults($address, &$locations)
    {
        $latlon = $this->geocodeAddress($address);

        //Call usort with anonymous comparator function
        usort($locations, function ($a, $b) use ($latlon) {
            $locA = array_map('floatval', array_map('trim', explode(',', $a['latlon'])));
            $locB = array_map('floatval', array_map('trim', explode(',', $b['latlon'])));

            $distA = $this->geoDistance($latlon['lat'], $latlon['lon'], $locA[0], $locA[1]);
            $distB = $this->geoDistance($latlon['lat'], $latlon['lon'], $locB[0], $locB[1]);

            if ($distA == $distB) {
                return 0;
            }
            return ($distA < $distB) ? -1 : 1;
        });
    }

    function compare($a, $b)
    {
        global $latlon;
        $locA = array_map("floatval", array_map("trim", explode(",", $a["latlon"])));
        $locB = array_map("floatval", array_map("trim", explode(",", $b["latlon"])));

        $distA = $this->geoDistance($latlon["lat"], $latlon["lon"], $locA[0], $locA[1]);
        $distB = $this->geoDistance($latlon["lat"], $latlon["lon"], $locB[0], $locB[1]);

        if ($distA == $distB) {
            return 0;
        }
        return ($distA < $distB) ? -1 : 1;
    }


    function geocodeCountry($name, $url_encode = true){
        if($url_encode)
            $name = urlencode($name);
        $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];(node["name:en"="' . $name . '"]["place"="country"];);out;';
        echo $overpass."<br>";
        return $this->returnOverpassData($overpass);
    }

    function geocodePlace($name, $lon = null, $lat = null, $type, $parent = null, $url_encode = true){
        if($url_encode) {
            $name = urlencode($name);
            $parent_name = urlencode($parent->getName());
        }
        if(!is_null($lon)&&!is_null($lat)){
            $lat_min = $lat - 0.5; $lat_max = $lat + 0.5;
            $lon_min = $lon - 0.5; $lon_max = $lon + 0.5;
            $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];node["name"="'.$name.'"]["place"="'.$type.'"]';
            if(!is_null($parent))
                $overpass .= '["is_in"="' . $parent_name . '"]';
            $overpass .= '(bbox='.$lat_min.','.$lon_min.','.$lat_max.','.$lon_max;
            echo $overpass.'<br>';
        }
        else{
            $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];(node["name"="' . $name . '"]["place"="'.$type.'"]';
            if(!is_null($parent))
                if($parent->getType()===1)
                    $overpass .= '["is_in:country"="' . $parent_name . '"]';
                else if($parent->getType()===2)
                    $overpass .= '["is_in:city"="' . $parent_name . '"]';
        }
        $overpass .= ';);out;';
        echo $overpass.'<br>';
        return $this->returnOverpassData($overpass);


        $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];(node["name"="' . $input[0] . '"];way["name"="' . $input[0] . '"];relation["name"="' . $input["0"] . '"]["is_in"="' . $input["1"] . '"];);out;';



    }

    function geocodeCity($name, $lon = null, $lat = null, $parent = null, $url_encode = true){
        return $this->geocodePlace($name, $lon, $lat, 'city', $parent, $url_encode);
    }

    function geocodeTown($name, $lon = null, $lat = null, $parent = null, $url_encode = true){
        return $this->geocodePlace($name, $lon, $lat, 'town', $parent, $url_encode);
    }

    function geocodeVillage($name, $lon = null, $lat = null, $parent = null, $url_encode = true){
        return $this->geocodePlace($name, $lon, $lat, 'village', $parent, $url_encode);
    }

    function geocodeHamlet($name, $lon = null, $lat = null, $parent = null, $url_encode = true){
        return $this->geocodePlace($name, $lon, $lat, 'hamlet', $parent, $url_encode);
    }

    function geocodeNameAndParent($name, $parent){
        $url_name = urlencode($name);
        $url_parent = urlencode($parent);
        $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];area[name="'.$url_parent.'"];(node[name="'.$url_name.'"](area););out;';
        echo $overpass;
        try {
            $html = file_get_contents($overpass);
            if($html!==false) {
                $result = json_decode($html, true); // "true" to get PHP array instead of an object
                $data = $result['elements'];
            }
        }
        catch(Exception $e){
            echo $e->getMessage()."<br>";
        }

        // elements key contains the array of all required elements
        if(!isset($data)||count($data) == 0) {
            echo "No places found! <br>";
            try {
                $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];area[name="' . $url_parent . '"];(node[name~"' . $url_name . '"](area););out;';
                $html = file_get_contents($overpass);
                if($html!==false) {
                    $result = json_decode($html, true); // "true" to get PHP array instead of an object
                    // elements key contains the array of all required elements
                    $data = $result['elements'];
                }
            }
            catch(Exception $e){
                echo $e->getMessage()."<br>";
            }
        }
        if(!isset($data)) {
            echo "No places found! <br>";
            return null;
        }
        else if(count($data) !== 0) {
            foreach($result['elements'] as $element){
                if (array_key_exists('lat', $element))
                    $lat = $element['lat'];

                // longitude
                if (array_key_exists('lon', $element))
                    $lng = $element['lon'];
                if(isset($lat) && isset($lng)){
                    break;
                }
            }
            if(isset($lat) && isset($lng))
                return array ('lat' => $lat, 'lng' => $lng);
            else
                return null;
        }
        else{
            return null;
        }


    }

    function getPlaceType($name, $lon, $lat){
        $name = urlencode($name);
        $lat_min = $lat - 0.5; $lat_max = $lat + 0.5;
        $lon_min = $lon - 0.5; $lon_max = $lon + 0.5;

        $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];node["name"="'.$name.'"](bbox='.$lat_min.','.$lon_min.','.$lat_max.','.$lon_max.');out;';
        $type = $this->returnOverpassData($overpass)['type'];

        if ($type === "country") {
            echo "The place is a country.<br>";
            return 1;
        } else if ($type === "city") {
            echo "The place is a city.<br>.";
            return 2;
        } else if ($type === "town") {
            echo "The place is a town.<br>.";
            return 3;
        } else if ($type === "village") {
            echo "The place is a village.<br>.";
            return 4;
        }
        else if ($type === "hamlet") {
            echo "The place is a hamlet.<br>.";
            return 5;
        }
        else if ($type === "neighbourhood") {
            echo "The place is a neighbourhood.<br>";
            return 6;
        }
        else if ($type === "suburb") {
            echo "The place is a suburb.<br>";
            return 13;
        }
        else return null;
    }

    function geocodeAddress($address, $parent_name = false)
    {
        $input = explode(",", $address);
        //echo "Address: " . $address;
        $url_address = urlencode($address);
        $url_parent = urlencode($parent_name);
        if ($parent_name === 0) {
            print_r($input);
            if (count($input) == 1) {
                $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];(node["name"="' . $url_address . '"];way["name"="' . $url_address . '"];relation["name"="' . $url_address . '"];);out;';
            } else {
                $input[0] = urlencode(trim($input[0]));
                $input[1] = urlencode(trim($input[1]));
                $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];(node["name"="' . $input[0] . '"];way["name"="' . $input[0] . '"];relation["name"="' . $input["0"] . '"]["is_in"="' . $input["1"] . '"];);out;';
            }
        } else {
            $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];(node["name"="' . $url_address . '"];way["name"="' . $url_address . '"];relation["name"="' . $url_address . '"]["is_in"="' . $url_parent . '"];);out;';
        }
        echo $overpass."<br>";
        $geoData = $this->returnOverpassData($overpass);
        if(!is_null($geoData)){
            echo "Found data through overpass: "; print_r($geoData); echo "<br>";
            return $geoData;
        }
        else{
            $geoNames = 'http://api.geonames.org/search?username=jacoborrje&style=FULL&q=' . $url_address;
            echo "Found no corresponding place in Overpass. Searching in GeoNames: " . 'http://api.geonames.org/search?username=jacoborrje&style=FULL&q=' . $url_address . "<br>";
            $geoData = $this->returnGeonamesData($geoNames, $address);
            echo "Found data in GeoNames: "; print_r($geoData); echo "<br>";
            $geoData['type'] = 5;
            return $geoData;
        }
    }

    function placeFromAddress($address){
        $geoCode = $this->geocodeAddress($address);
        $place = new Place();
        $place->setName($address);
        if(!is_null($geoCode)){
            if(array_key_exists('lon', $geoCode))
                $place->setLng($geoCode['lon']);
            if(array_key_exists('lat', $geoCode))
                $place->setLat($geoCode['lat']);
            if(array_key_exists('type', $geoCode))
                $place->setType($geoCode['type']);
            if(array_key_exists('country', $geoCode)) {
                $parent = $this->placeRepository->findOneByNameAndAltNames(['name' => $geoCode['country']]);
                if (is_null($parent)) {
                    $parent = $this->countryPlaceFromAddress($geoCode['country']);
                }
                if (!is_null($parent)) {
                    $place->setParent($parent);
                }
            }
            return $place;
        }
        else
            return null;
    }

    function countryPlaceFromAddress($address){
        $geoCode = $this->geocodeCountry($address);
        if(!is_null($geoCode)) {
            $country = new Place();
            $country->setName($address);
            $country->setLng($geoCode['lon']);
            $country->setLat($geoCode['lat']);
            $country->setType(1);
            return $country;
        }
        else
            return null;
    }

    function returnGeonamesData($geoNames, $name){
        $this->geonamesCrawler = $this->client->request('GET', $geoNames);
        if($this->geonamesCrawler->filterXPath('//geonames/geoname[contains(toponymName/text(), "'.$name.'")]')->count()){
            echo 'Searching for XPath: //geonames/geoname[contains(toponymName/text(), "'.$name.'")]<br>';
            $geoName = $this->geonamesCrawler->filterXPath('//geonames/geoname[contains(toponymName/text(), "'.$name.'")]');
            //echo $geoName->html();
            $lat = $geoName->children('lat')->text();
            $lng = $geoName->children('lng')->text();
            $alt_names = $geoName->children('alternateNames')->text();
            $country = $geoName->children('countryName')->text();
            $name = $geoName->children('toponymName')->text();



            return ['lat' => $lat, 'lng' => $lng, 'alt_names' => $alt_names, 'country' => $country, 'name' =>$name];
        }
        else
            return null;
    }

    function returnOverpassData($overpass){
        $html = file_get_contents($overpass);
        $result = json_decode($html, true); // "true" to get PHP array instead of an object

        // elements key contains the array of all required elements
        $data = $result['elements'];

        foreach ($data as $key => $row) {

            // latitude
            if (array_key_exists('lat', $row))
                $lat = $row['lat'];

            // longitude
            if (array_key_exists('lon', $row))
                $lng = $row['lon'];

            // type
            if ((array_key_exists('tags', $row))) {
                if ((array_key_exists('place', $row['tags']))) {
                    $type = $row['tags']['place'];
                }
                if (array_key_exists('is_in:country', $row['tags'])) {
                    $country = $row['tags']['is_in:country'];
                }
                if (array_key_exists('name', $row['tags'])) {
                    $name = $row['tags']['name'];
                }
            }
            break;
        }
        if (isset($lat) && isset($lng) && isset($type) && isset($name)) {
            if ($type === "country") {
                echo "The place is a country.<br>";
                $type = 1;
            } else if ($type === "city") {
                echo "The place is a city.<br>.";
                $type = 2;
            } else if ($type === "town") {
                echo "The place is a town.<br>.";
                $type = 3;
            } else if ($type === "village") {
                echo "The place is a village.<br>.";
                $type = 4;
            }
            else if ($type === "hamlet") {
                echo "The place is a hamlet.<br>.";
                $type = 5;
            }
            else if ($type === "neighbourhood") {
                echo "The place is a neighbourhood.<br>";
                $type = 6;
            }
            if(isset($country))
                return array('lat' => $lat, 'lon' => $lng, 'type' => $type, 'name' => $name, 'country' => $country);
            else
                return array('lat' => $lat, 'lon' => $lng, 'type' => $type, 'name' => $name);
        }
        else
            return null;
        }
}
?>
