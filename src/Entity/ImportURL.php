<?php

namespace App\Entity;

use \DateTime;

class FuzzyDate
{

    private $full_date;
    private $accuracy;
    private $date_string;


    public function setByDateString($input_string){
        $length = strlen($input_string);

        if($length == 4){
            if(strcmp($input_string,"9999")===0){
                $this->date_string = "????";
                $formated_date = null;
            }
            else if (strcmp($input_string,"0000")===0){
                $this->date_string = "????";
                $formated_date = null;
            }
            else if (strcmp($input_string,"????")===0){
                $this->date_string = "????";
                $formated_date = null;
            }
            else {
                $this->date_string = $input_string;
                $formated_date = $input_string."-01-01";
            }
            if(!is_null($formated_date)) {
                $this->full_date = DateTime::createFromFormat('Y-m-d', $formated_date);
                $this->accuracy = 2;
            }
            else{
                $this->full_date = null;
                $this->accuracy = null;
            }
        }
        else if($length == 7){
            $this->date_string = $input_string;
            $formated_date= $input_string."-01";
            $this->full_date  = DateTime::createFromFormat('Y-m-d', $formated_date);
            $this->accuracy = 1;
        }
        else if($length == 10){
            $this->date_string = $input_string;
            $formated_date= $input_string;
            $this->full_date  = DateTime::createFromFormat('Y-m-d', $formated_date);
            $this->accuracy = 0;
        }
        else{
            $this->date_string = null;
            $this->full_date = null;
            $this->accuracy = null;
        }
    }


    public function setByDateAccuracy($date = null, $accuracy = null)
    {
        if($date !== null && $accuracy !== null) {
            $this->accuracy = $accuracy;
            $this->full_date = $date;
            $formated_date = $date->format('Y-m-d');
            if ($accuracy == 0) {
                $this->date_string = $formated_date;
            } else if ($accuracy == 1) {
                $this->date_string = substr($formated_date, 0, 7);
            } else if ($accuracy == 2) {
                if (strcmp($formated_date, "9999") === 0) $this->date_string = "????";
                else if (strcmp($formated_date, "0000") === 0) $this->date_string = "????";
                else $this->date_string = substr($formated_date, 0, 4);
            }
        }
        else{
            $this->accuracy = 2;
            $this->full_date = null;
            $this->date_string = "????";
        }
        return $this;
    }

    public function getDateString()
    {
        return $this->date_string;
    }

    public function getFullDate()
    {
        return $this->full_date;
    }

    public function getAccuracy(){
        return $this->accuracy;
    }
}
