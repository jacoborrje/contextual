<?php
namespace App\Utils;

use \App\Entity\Place;
use \App\Utils\GeographyHelper;


class PlaceService
{
    private $geographyHelper;

    function __construct(GeographyHelper $geographyHelper)
    {
        $this->geographyHelper = $geographyHelper;
    }

    function merge(Place $existingPlace, Place $newPlace)
    {
        if(is_null($existingPlace->getParent()) && !is_null($newPlace->getParent())) {
            $existingPlace->setParent($newPlace->getParent());
        }
        if(($existingPlace->getAltNames()=== '' ||is_null($existingPlace->getAltNames())) && !is_null($newPlace->getAltNames())){
            $existingPlace->setAltNames($newPlace->getAltNames());
        }
        if(($existingPlace->getAlvinId()=== '' || is_null($existingPlace->getAlvinId())) && !is_null($newPlace->getAlvinId())){
            $existingPlace->setAlvinId($newPlace->getAlvinId());
        }
        if(($existingPlace->getType()=== '' || is_null($existingPlace->getType())) && !is_null($newPlace->getType())){
            $existingPlace->setType($newPlace->getType());
        }
        return $existingPlace;
    }

    function refinePlace(Place $place){
        if(is_null($place->getLng())||is_null($place->getLat())) {
            if ($place->getType() === 1) {
                echo "Geocoding country!<br>";
                $placeData = $this->geographyHelper->geocodeCountry($place->getName(), false);
                print_r($placeData); echo "<br>";
            }
            else if ($place->getType() === 2)
                $placeData = $this->geographyHelper->geocodeCity($place->getName(), null, null, $place->getParent());
            else if ($place->getType() === 3)
                $placeData = $this->geographyHelper->geocodeTown($place->getName(), null, null, $place->getParent());
            else if ($place->getType() === 4)
                $placeData = $this->geographyHelper->geocodeVillage($place->getName(), null, null, $place->getParent());
            else if ($place->getType() === 5)
                $placeData = $this->geographyHelper->geocodeHamlet($place->getName(), null, null, $place->getParent());
            else {
                $placeData = $this->geographyHelper->geocodePlace($place->getName(), null, null, null, $place->getParent());
            }
            if (is_null($place->getParent())&&!is_null($placeData['type'])) {
                $place->setType($placeData['type']);
            }
        }

        if(isset($placeData)){
            $place->setLng($placeData['lon']);
            $place->setLat($placeData['lat']);
        }
        return $place;
    }
}





?>


