<?php
namespace App\Utils;

use \App\Entity\Place;


class PlaceService
{

    function merge(Place $existingPlace, Place $newPlace)
    {
        if(is_null($existingPlace->getParent()) && !is_null($newPlace->getParent())) {
            $existingPlace->setParent($newPlace->getParent());
        }
        if($existingPlace->getAltNames()=== '' ||is_null($existingPlace->getAltNames()) && $newPlace->getAltNames()!== ''){
            $existingPlace->setAltNames($newPlace->getAltNames());
        }
        if($existingPlace->getAlvinId()=== '' || is_null($existingPlace->getAlvinId()) && $newPlace->getAlvinId()!== ''){
            $existingPlace->setAlvinId($newPlace->getAlvinId());
        }
        if($existingPlace->getType()=== '' || is_null($existingPlace->getType()) && $newPlace->getType()!== ''){
            $existingPlace->setType($newPlace->getType());
        }
        return $existingPlace;
    }
}
?>
