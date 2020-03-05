<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-02-28
 * Time: 18:55
 */

namespace App\Entity;

use \DateTime;
use \Exception;

class TimelineEntry
{
    private $date, $correspondents, $verb, $directObject, $indirectObject;
    private $directObjectType, $indirectObjectType;
    private $directObjectPrep, $indirectObjectPrep;
    private $entryString;


    private $verbList = array(
      'travelled' => ['directObject' => 'Place',             'indirectObject' => 'Place',
                      'directPrep' => 'to',                  'indirectObjectPrep' => 'from'],
      'moved'     => ['directObject' => 'Place',
                      'directPrep' => 'to'],
      'joined'       => ['directObject' => 'Institution',    'indirectObject' => 'null'],
      'was born'     => ['directObject' => 'Place',          'indirectObject' => 'string',
                         'directObjectPrep' => 'in'],
      'died'         => ['directObject' => 'Place',          'indirectObject' => 'string',
                         'directObjectPrep' => 'in'],
      'wrote'        => ['directObject' => 'Source',         'indirectObject' => 'Action[]',
                        'directObjectPrep' => 'a',           'indirectObjectPrep' => 'to'],
      'received'     => ['directObject' => 'Source',         'indirectObject' => 'Action[]',
            'directObjectPrep' => 'a',           'indirectObjectPrep' => 'from'],
      'became'       => ['directObject' => 'Occupation',     'indirectObject' => 'Institution',
                        'directPrep' => 'a',                 'indirectObjectPrep' => 'in']
    );

    /**
     * @return mixed
     */
    function __construct(DateTime $date = null, $primarySubject, $subjects, $verb, $directObject, $indirectObject)
    {
        $this->correspondents = $subjects;
        $this->date = $date;

        foreach($this->verbList as $key => $verbCandidate){
            if($verb === $key){
                $this->directObjectType = $verbCandidate['directObject'];
                $this->verb = $verb;
                if(array_key_exists('directObjectPrep', $verbCandidate))
                    $this->directObjectPrep = $verbCandidate['directObjectPrep'];
                $this->indirectObjectType = $verbCandidate['indirectObject'];
                if(array_key_exists('indirectObjectPrep', $verbCandidate))
                    $this->indirectObjectPrep = $verbCandidate['indirectObjectPrep'];
            }
        }

        $directObjectClass = gettype($directObject);
        if ($directObjectClass === 'object') {
            $directObjectClassArray = explode("\\", get_class($directObject));
            $directObjectClass = end($directObjectClassArray);
        }

        if(substr_compare( $this->directObjectType,"[]", -2)===0){
            $acceptedClassInArray = substr($this->indirectObjectType, 0,strlen($this->indirectObjectType)-2);
            if($directObjectClass!=='ArrayCollection'){
                if ($directObjectClass === $acceptedClassInArray){
                    $this->directObject = [$directObject];
                }
                else{
                    throw new Exception('Verb "' . $this->verb . '" only accepts an array of ' . explode("[]", $this->directObjectType)[0] . "s as a direct object. Got ". $directObjectClass.".");
                }
            }
            else{
                //check that the array contains the right objects
                foreach($directObject as $object){
                    $objectClass = explode("\\", get_class($object));
                    $objectClass = end($objectClass);
                    if(!($objectClass === $acceptedClassInArray)){
                        throw new Exception('The verb "'.$this->verb.'" only accepts an array of ' . $acceptedClassInArray . "s as a direct object. Found a(n) " . $objectClass . " in the submitted array.");
                    }
                }
                $this->indirectObject = $indirectObject;
            }
        }
        else {
            if ($this->directObjectType !== 'null') {
                if ($directObjectClass === $this->directObjectType) {
                    $this->directObject = $directObject;
                } else {
                    throw new Exception('Verb "' . $this->verb . '" only accepts objects of type ' . $this->directObjectType . " as a direct object. Got " . $directObjectClass . ".");
                }
            }
        }

        $indirectObjectClass = gettype($indirectObject);
        if ($indirectObjectClass === 'object') {
            $indirectObjectClassArray = explode("\\", get_class($indirectObject));
            $indirectObjectClass = end($indirectObjectClassArray);
        }

        if(substr_compare( $this->indirectObjectType,"[]", -2)===0){
            $acceptedClassInArray = substr($this->indirectObjectType, 0,strlen($this->indirectObjectType)-2);
            if($indirectObjectClass!=='ArrayCollection'){
                if ($indirectObjectClass === $acceptedClassInArray){
                    $this->indirectObject = [$indirectObject];
                }
                else {
                    throw new Exception('Verb "' . $this->verb . '" only accepts an array of ' . explode("[]", $this->indirectObjectType)[0] . "s as an indirect object. Got " . $indirectObjectClass . ".");
                }
            }
            else{
                //check that the array contains the right objects
                foreach($indirectObject as $object){
                    $objectClass = explode("\\", get_class($object));
                    $objectClass = end($objectClass);
                    if(!($objectClass === $acceptedClassInArray)){
                        throw new Exception('The verb "'.$this->verb.'" only accepts an array of ' . $acceptedClassInArray . "s as an indirect object. Found a(n) " . $objectClass . " in the submitted array.");
                    }
                }
                $this->indirectObject = $indirectObject;
            }
        }
        else{
            if ($this->indirectObjectType !== 'null') {
                if ($indirectObjectClass === $this->indirectObjectType) {
                    $this->indirectObject = $indirectObject;
                } else {
                    throw new Exception('Verb ' . $this->verb . ' only accepts objects of type ' . $this->indirectObjectType . " as an indirect object. Got " . $indirectObjectClass . ".");
                }
            }
        }

        $company = [];

        foreach($this->correspondents as $correspondent){
            if($primarySubject !== $correspondent){
                $company[] = $correspondent;
            }
        }

        $entryString = (string) $primarySubject;
        $entryString .= " ".$this->verb;
        if($this->directObjectType!=='null') {
            if(!is_null($this->directObjectPrep)){
                $entryString .= " " . $this->directObjectPrep;
            }
            if($directObjectClass==='Source')
                $entryString .= " " . $this->directObject->getTypeString();
            else if ($directObjectClass==='Correspondent') {
                $entryString .= " " . $this->directObject;
            }
            else{
                $entryString .= " " . $this->directObject;
            }
        }
        if($this->indirectObjectType!=='null') {
            if(!is_null($this->indirectObjectPrep)){
                $entryString .= " ".$this->indirectObjectPrep;
            }
            if ($this->indirectObjectType==='Action[]') {
                $recipientString = "";
                $i = 0;
                foreach ($this->indirectObject as $action){
                    $recipientString = $recipientString . $action->getCorrespondent() . ", ";
                }
                $recipientString = substr($recipientString,0,-2);
                $entryString .= " " . $recipientString;
            }
            else {
                $entryString .= " " . $this->indirectObject;
            }
        }
        if(count($company)>0){
            $i = 0;
            foreach($company as $fellowSubject){
                if($i === 1){
                    $entryString .= " with ";
                    $entryString .= (string) $fellowSubject;
                }
                else if($i == count($company) && count($company)>1){
                    $entryString .= " and ". (string) $fellowSubject;
                }
                else{
                    $entryString .= ", ". (string) $fellowSubject;
                }
            }
        }
        $entryString .= ".";
        $this->entryString = $entryString;
        return $this;
    }

    public function __toString()
    {
        return $this->entryString;
    }

    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getCorrespondents()
    {
        return $this->correspondents;
    }

    /**
     * @param mixed $correspondents
     */
    public function setCorrespondents(Correspondent $correspondents)
    {
        $this->correspondents = $correspondents;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getEntryString($subject){
        return $this->entryString;
    }
}