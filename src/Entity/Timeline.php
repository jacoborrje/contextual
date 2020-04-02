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
use App\Entity\TimelineEntry;

class Timeline
{
    private $actor;
    private $institution;
    private $timeLineEntries;

    /**
     * @return mixed
     */
    function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * @param mixed $actor
     */
    public function setActor($actor)
    {
        $this->actor = $actor;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    function addEntry(TimelineEntry $entry){
        $this->timeLineEntries[] = $entry;
    }

    function setEntries($entries){
        $this->timeLineEntries = $entries;
    }

    function getEntries(){
        return $this->timeLineEntries;
    }

    function removeEntry(TimelineEntry $entryToRemove){
        $this->timeLineEntries->remove($entryToRemove);
    }

    function __toString()
    {
        // TODO: Implement __toString() method.
    }

    function sort(){
        if(!empty($this->timeLineEntries)) {
            usort($this->timeLineEntries, function ($a1, $a2) {
                if (!is_null(($a1->getDate()))) {
                    $date1 = $a1->getDate();
                } else {
                    $date1 = DateTime::createFromFormat('Y-d-m', '0000-01-01');
                }
                if (!is_null(($a2->getDate()))) {
                    $date2 = $a2->getDate();
                } else {
                    $date2 = DateTime::createFromFormat('Y-d-m', '0000-01-01');
                }

                if ($date1 == $date2) {
                    if (!is_null(($a1->getTime()))) {
                        $time1 = $a1->getTime();
                    }
                    else $time1 = 0;
                    if (!is_null(($a2->getTime()))) {
                        $time2 = $a2->getTime();
                    }
                    else $time2 = 0;
                    if ($time1 == $time2) {
                        return 0;
                    }
                    return $time1 < $time2 ? -1 : 1;
                }
                return $date1 < $date2 ? -1 : 1;
            });
        }
    }

    function removeUndated(){
        $entries = [];
        foreach($this->timeLineEntries as $entry){
            if(!is_null($entry->getDate)){
                $entries[] = $entry;
            }
        }
        $this->setEntries($entries);
    }

    function removeUngeographical(){
        $entries = [];
        if(isset($this->timeLineEntries)) {
            foreach ($this->timeLineEntries as $entry) {
                if (!is_null($entry->getLon()) && !is_null($entry->getLat())) {
                    $entries[] = $entry;
                }
            }
        }
        $this->setEntries($entries);
    }

    function getMaxLon(){
        $maxLon = null;
        foreach($this->timeLineEntries as $entry){
            if(is_null($maxLon)){
                $maxLon = $entry->getLon();
            }
            else if($entry->getLon()>$maxLon){
                $maxLon = $entry->getLon();
            }
        }
        return $maxLon;
    }

    function getMinLon(){
        $minLon = null;
        foreach($this->timeLineEntries as $entry){
            if(is_null($minLon)){
                $minLon = $entry->getLon();
            }
            else if($entry->getLon()<$minLon){
                $minLon = $entry->getLon();
            }
        }
        return $minLon;
    }

    function getMaxLat(){
        $maxLat= null;
        foreach($this->timeLineEntries as $entry){
            if(is_null($maxLat)){
                $maxLat = $entry->getLat();
            }
            else if($entry->getLat()>$maxLat){
                $maxLat = $entry->getLat();
            }
        }
        return $maxLat;
    }

    function getMinLat(){
        $minLat = null;
        foreach($this->timeLineEntries as $entry){
            if(is_null($minLat)){
                $minLat = $entry->getLat();
            }
            else if($entry->getLat()<$minLat){
                $minLat = $entry->getLat();
            }
        }
        return $minLat;
    }
}