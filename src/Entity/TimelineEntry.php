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
use App\Entity\FuzzyDate;

class TimelineEntry
{
    private $date, $dateAccuracy, $correspondents, $verb, $directObject, $indirectObject, $company, $primarySubject;
    private $directObjectType, $indirectObjectType;
    private $directObjectPrep, $indirectObjectPrep;
    private $directObjectClass, $indirectObjectClass;
    private $initialText, $displaySubjectLink;
    private $source;
    private $sourcePages;
    private $lon, $lat;
    private $entryString;
    private $icon_url;
    private $time;

    private $verbList = array(
        //Verbs for actors
      'travelled' => ['directObject' => 'Place',             'indirectObject' => 'Place',
                      'directObjectPrep' => 'to',                  'indirectObjectPrep' => 'from'],
        'returned' => ['directObject' => 'Place',             'indirectObject' => 'Place',
            'directObjectPrep' => 'to',                  'indirectObjectPrep' => 'from'],
      'moved'     => ['directObject' => 'Place',
                      'directObjectPrep' => 'to'],
      'joined'       => ['directObject' => 'Institution',    'indirectObject' => 'Occupation',
                                                             'indirectObjectPrep' => 'as a'],
      'was born'     => ['directObject' => 'Place',
                         'directObjectPrep' => 'in'],
        'married'     => ['directObject' => 'Correspondent', 'indirectObject' => 'Place',
        'indirectObjectPrep' => 'in'],
      'died'         => ['directObject' => 'Place',
                         'directObjectPrep' => 'in'],
      'wrote'        => ['directObject' => 'Source',         'indirectObject' => 'Action[]',
                        'directObjectPrep' => 'a',           'indirectObjectPrep' => 'to'],
      'received'     => ['directObject' => 'Source',         'indirectObject' => 'Action[]',
            'directObjectPrep' => 'a',           'indirectObjectPrep' => 'from'],
      'became'       => ['directObject' => 'Occupation',     'indirectObject' => 'Institution',
                        'directObjectPrep' => 'a',                 'indirectObjectPrep' => 'at'],
        'stopped being'   => ['directObject' => 'Occupation',     'indirectObject' => 'Institution',
            'directObjectPrep' => 'a',                 'indirectObjectPrep' => 'at'],
        'was mentioned'   => ['directObject' => 'Source',         'indirectObject' => 'Action[]',
                              'directObjectPrep' => 'in a',           'indirectObjectPrep' => 'by'],
        'visited'   => ['directObject' => 'Correspondent',         'indirectObject' => 'Place',
                                                                'indirectObjectPrep' => 'in'],
        'dined'   => ['directObject' => 'Institution',         'indirectObject' => 'Place',
                    'directObjectPrep' => 'at',              'indirectObjectPrep' => 'in'],
        'slept'   => ['directObject' => 'Institution',         'indirectObject' => 'Place',
            'directObjectPrep' => 'at',              'indirectObjectPrep' => 'in'],
        'rested'   => ['directObject' => 'Institution',         'indirectObject' => 'Place',
            'directObjectPrep' => 'at',              'indirectObjectPrep' => 'in'],
        'arrived'   => ['directObject' => 'Place',
            'directObjectPrep' => 'in'],
        'passed'   => ['directObject' => 'Place',
                     'directObjectPrep' => 'through'],
        'acquired' =>['directObject' => 'Place', 'indirectObject' => 'Correspondent',
            'inDirectObjectPrep' => 'from'],
        'departed' => ['directObject' => 'Place',             'indirectObject' => 'Place',
            'directObjectPrep' => 'from',                  'indirectObjectPrep' => 'to'],


        //Verbs for institutions
        'was established'     => ['directObject' => 'Place',          'indirectObject' => 'string',
            'directObjectPrep' => 'in'],
        'was dissolved'         => ['directObject' => 'Place',          'indirectObject' => 'string',
            'directObjectPrep' => 'in'],
        //Verbs for relationships
    );

    /**
     * @return mixed
     */
    function __construct(DateTime $date = null, int $dateAccuracy = null, $primarySubject, $subjects, $verb, $directObject = null, $indirectObject = null, $initialText = null, $displaySubjectLink = false, $source = null, $sourcePages = null, $time = null)
    {
        $this->correspondents = $subjects;
        $this->date = $date;
        $this->dateAccuracy = $dateAccuracy;
        $this->initialText = $initialText;
        $this->displaySubjectLink = $displaySubjectLink;
        $this->source = $source;
        $this->sourcePages = $sourcePages;
        $this->time = $time;

        foreach ($this->verbList as $key => $verbCandidate) {

            if ($verb === $key) {
                $this->verb = $verb;
                $this->primarySubject = $primarySubject;
                if (array_key_exists('directObject', $verbCandidate))
                    $this->directObjectType = $verbCandidate['directObject'];
                if (array_key_exists('directObjectPrep', $verbCandidate))
                    $this->directObjectPrep = $verbCandidate['directObjectPrep'];
                if (array_key_exists('indirectObject', $verbCandidate))
                    $this->indirectObjectType = $verbCandidate['indirectObject'];
                if (array_key_exists('indirectObjectPrep', $verbCandidate))
                    $this->indirectObjectPrep = $verbCandidate['indirectObjectPrep'];
            }
        }

        if (!is_null($directObject)) {
            $directObjectClass = gettype($directObject);
            if ($directObjectClass === 'object') {
                $directObjectClassArray = explode("\\", get_class($directObject));
                $directObjectClass = end($directObjectClassArray);
            }
        }


        if (!is_null($this->directObjectType) && !is_null($directObject)) {
            if (substr_compare($this->directObjectType, "[]", -2) === 0) {
                $acceptedClassInArray = substr($this->indirectObjectType, 0, strlen($this->indirectObjectType) - 2);
                if ($directObjectClass !== 'ArrayCollection') {
                    if ($directObjectClass === $acceptedClassInArray) {
                        $this->directObject = [$directObject];
                    } else {
                        throw new Exception('Verb "' . $this->verb . '" only accepts an array of ' . explode("[]", $this->directObjectType)[0] . "s as a direct object. Got " . $directObjectClass . ".");
                    }
                } else {
                    //check that the array contains the right objects
                    foreach ($directObject as $object) {
                        $objectClass = explode("\\", get_class($object));
                        $objectClass = end($objectClass);
                        if (!($objectClass === $acceptedClassInArray)) {
                            throw new Exception('The verb "' . $this->verb . '" only accepts an array of ' . $acceptedClassInArray . "s as a direct object. Found a(n) " . $objectClass . " in the submitted array.");
                        }
                    }
                    $this->indirectObject = $indirectObject;
                }
            } else {
                if (!is_null($this->directObjectType)) {
                    if ($directObjectClass === $this->directObjectType) {
                        $this->directObject = $directObject;
                    } else if ($directObjectClass !== 'NULL') {
                        throw new Exception('Verb "' . $this->verb . '" only accepts objects of type ' . $this->directObjectType . " as a direct object. Got " . $directObjectClass . ".");
                    }
                }
            }
        }

        if (!is_null($indirectObject)) {
            $indirectObjectClass = gettype($indirectObject);
            if ($indirectObjectClass === 'object') {
                $indirectObjectClassArray = explode("\\", get_class($indirectObject));
                $indirectObjectClass = end($indirectObjectClassArray);
            }
        }

        if (!is_null($this->indirectObjectType) && !is_null($indirectObject)) {
            if (substr_compare($this->indirectObjectType, "[]", -2) === 0) {
                $acceptedClassInArray = substr($this->indirectObjectType, 0, strlen($this->indirectObjectType) - 2);
                if ($indirectObjectClass !== 'ArrayCollection') {
                    if ($indirectObjectClass === $acceptedClassInArray) {
                        $this->indirectObject = [$indirectObject];
                    } else {
                        throw new Exception('Verb "' . $this->verb . '" only accepts an array of ' . explode("[]", $this->indirectObjectType)[0] . "s as an indirect object. Got " . $indirectObjectClass . ".");
                    }
                } else {
                    //check that the array contains the right objects
                    foreach ($indirectObject as $object) {
                        $objectClass = explode("\\", get_class($object));
                        $objectClass = end($objectClass);
                        if (!($objectClass === $acceptedClassInArray)) {
                            throw new Exception('The verb "' . $this->verb . '" only accepts an array of ' . $acceptedClassInArray . "s as an indirect object. Found a(n) " . $objectClass . " in the submitted array.");
                        }
                    }
                    $this->indirectObject = $indirectObject;
                }
            } else {
                if ($this->indirectObjectType !== 'NULL') {
                    if ($indirectObjectClass === $this->indirectObjectType) {
                        $this->indirectObject = $indirectObject;
                    } else if ($indirectObjectClass !== 'NULL') {
                        throw new Exception('Verb ' . $this->verb . ' only accepts objects of type ' . $this->indirectObjectType . " as an indirect object. Got " . $indirectObjectClass . ".");
                    }
                }
            }
        }

        $company = [];
        foreach ($this->correspondents as $correspondent) {
            if ($primarySubject !== $correspondent) {
                $company[] = $correspondent;
            }
        }
        $this->company = $company;
        if (isset($directObjectClass)) {
            $this->directObjectClass = $directObjectClass;

            if ($directObjectClass === "Place" && !is_null($this->getDirectObject())) {
                $this->setLon($this->getDirectObject()->getLng());
                $this->setLat($this->getDirectObject()->getLat());
            }
        }
        if (isset($indirectObjectClass)) {
            $this->indirectObjectClass = $indirectObjectClass;

            if ($indirectObjectClass === "Place") {
                $this->setLon($this->getIndirectObject()->getLng());
                $this->setLat($this->getIndirectObject()->getLat());
            }
        }

        return $this;
    }


    public function getTextDate(){
        if(!is_null($this->date)&&!is_null($this->dateAccuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->date, $this->dateAccuracy);
            return $fuzzy_date->getDateString();
        }
        else
            return "undated";
    }

    public function __toString()
    {
        return $this->getEntryString();
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

    public function getEntryString($addLinks = false){
        return $this->entryString;
    }

    public function setEntryString($entryString){
        $this->entryString = $entryString;
        return $this;
    }

    /**
     * @return int|string
     */
    public function getVerb()
    {
        return $this->verb;
    }

    /**
     * @param int|string $verb
     */
    public function setVerb($verb)
    {
        $this->verb = $verb;
    }

    /**
     * @return mixed
     */
    public function getDirectObject()
    {
        return $this->directObject;
    }

    /**
     * @param mixed $directObject
     */
    public function setDirectObject($directObject)
    {
        $this->directObject = $directObject;
    }

    /**
     * @return mixed
     */
    public function getIndirectObject()
    {
        return $this->indirectObject;
    }

    /**
     * @param mixed $indirectObject
     */
    public function setIndirectObject($indirectObject)
    {
        $this->indirectObject = $indirectObject;
    }

    /**
     * @return array
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param array $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return mixed
     */
    public function getPrimarySubject()
    {
        return $this->primarySubject;
    }

    /**
     * @param mixed $primarySubject
     */
    public function setPrimarySubject($primarySubject)
    {
        $this->primarySubject = $primarySubject;
    }

    /**
     * @return mixed
     */
    public function getDirectObjectType()
    {
        return $this->directObjectType;
    }

    /**
     * @param mixed $directObjectType
     */
    public function setDirectObjectType($directObjectType)
    {
        $this->directObjectType = $directObjectType;
    }

    /**
     * @return mixed
     */
    public function getIndirectObjectType()
    {
        return $this->indirectObjectType;
    }

    /**
     * @param mixed $indirectObjectType
     */
    public function setIndirectObjectType($indirectObjectType)
    {
        $this->indirectObjectType = $indirectObjectType;
    }

    /**
     * @return mixed
     */
    public function getDirectObjectPrep()
    {
        return $this->directObjectPrep;
    }

    /**
     * @param mixed $directObjectPrep
     */
    public function setDirectObjectPrep($directObjectPrep)
    {
        $this->directObjectPrep = $directObjectPrep;
    }

    /**
     * @return mixed
     */
    public function getIndirectObjectPrep()
    {
        return $this->indirectObjectPrep;
    }

    /**
     * @param mixed $indirectObjectPrep
     */
    public function setIndirectObjectPrep($indirectObjectPrep)
    {
        $this->indirectObjectPrep = $indirectObjectPrep;
    }

    /**
     * @return bool|mixed|string
     */
    public function getDirectObjectClass()
    {
        return $this->directObjectClass;
    }

    /**
     * @param bool|mixed|string $directObjectClass
     */
    public function setDirectObjectClass($directObjectClass)
    {
        $this->directObjectClass = $directObjectClass;
    }

    /**
     * @return bool|mixed|string
     */
    public function getIndirectObjectClass()
    {
        return $this->indirectObjectClass;
    }

    /**
     * @param bool|mixed|string $indirectObjectClass
     */
    public function setIndirectObjectClass($indirectObjectClass)
    {
        $this->indirectObjectClass = $indirectObjectClass;
    }

    /**
     * @return mixed
     */
    public function getIconUrl()
    {
        return $this->icon_url;
    }

    /**
     * @param mixed $icon_url
     */
    public function setIconUrl($icon_url)
    {
        $this->icon_url = $icon_url;
    }

    /**
     * @return array
     */
    public function getVerbList()
    {
        return $this->verbList;
    }

    /**
     * @param array $verbList
     */
    public function setVerbList($verbList)
    {
        $this->verbList = $verbList;
    }

    /**
     * @return bool
     */
    public function displaySubjectLink()
    {
        return $this->displaySubjectLink;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source): void
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getLon()
    {
        return $this->lon;
    }

    /**
     * @param mixed $lon
     */
    public function setLon($lon): void
    {
        $this->lon = $lon;
    }

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param mixed $lat
     */
    public function setLat($lat): void
    {
        $this->lat = $lat;
    }

    /**
     * @return null
     */
    public function getSourcePages()
    {
        return $this->sourcePages;
    }

    /**
     * @param null $sourcePages
     */
    public function setSourcePages($sourcePages): void
    {
        $this->sourcePages = $sourcePages;
    }

    /**
     * @return null
     */
    public function getInitialText()
    {
        return $this->initialText;
    }

    /**
     * @return null
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param null $time
     */
    public function setTime($time): void
    {
        $this->time = $time;
    }


}