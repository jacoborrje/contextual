<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-02-25
 * Time: 19:26
 */

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

function sortResults($address, &$locations) {
    $latlon = geocodeAddress($address);

    //Call usort with anonymous comparator function
    usort($locations, function($a, $b) use ($latlon) {
        $locA  = array_map('floatval', array_map('trim', explode(',', $a['latlon'])));
        $locB  = array_map('floatval', array_map('trim', explode(',', $b['latlon'])));

        $distA = geoDistance($latlon['lat'], $latlon['lon'], $locA[0], $locA[1]);
        $distB = geoDistance($latlon['lat'], $latlon['lon'], $locB[0], $locB[1]);

        if($distA == $distB) {
            return 0;
        }
        return ($distA < $distB) ? -1 : 1;
    });
}

function compare($a, $b) {
    global $latlon;
    $locA = array_map("floatval", array_map("trim", explode(",", $a["latlon"])));
    $locB = array_map("floatval", array_map("trim", explode(",", $b["latlon"])));

    $distA = geoDistance($latlon["lat"], $latlon["lon"], $locA[0], $locA[1]);
    $distB = geoDistance($latlon["lat"], $latlon["lon"], $locB[0], $locB[1]);

    if($distA == $distB) {
        return 0;
    }
    return ($distA < $distB) ? -1 : 1;
}


function geocodeAddress($address, $parent_name = false) {

    echo "Address: ". $address;

    if ($parent_name===0) {
        $input = explode(",", $address);
        print_r($input);
        if (count($input)==1){
            $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];(node["name"="'.$address.'"];way["name"="'.$address.'"];relation["name"="'.$address.'"];);out;';
        }
        else{
            $input[1]=trim($input[1]);
            $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];(node["name"="'.$input[0].'"];way["name"="'.$input[0].'"];relation["name"="'.$input["0"].'"]["is_in"="'.$input["1"].'"];);out;';
        }
    }
    else{
        $overpass = 'http://overpass-api.de/api/interpreter?data=[out:json];(node["name"="'.$address.'"];way["name"="'.$address.'"];relation["name"="'.$address.'"]["is_in"="'.$parent_name.'"];);out;';
    }

    echo $overpass;

        $html = file_get_contents($overpass);
    $result = json_decode($html, true); // "true" to get PHP array instead of an object

    // elements key contains the array of all required elements
    $data = $result['elements'];

    foreach($data as $key => $row) {

        // latitude
        $lat = $row['lat'];

        // longitude
        $lng = $row['lon'];
    }
    return array('lat'=> $lat, 'lon' => $lng);
}
?>
