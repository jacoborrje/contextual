<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RelationshipRepository")
 * @ORM\Table(name="relationships")
 */
class Relationship
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Actor", inversedBy="primaryRelationships")
     * @ORM\JoinColumn(nullable=false)
     */
    private $actor_1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Actor", inversedBy="secondaryRelationships")
     * @ORM\JoinColumn(nullable=false)
     */
    private $actor_2;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $start_date;

    private $text_start_date;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $end_date;

    private $text_end_date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $start_date_accuracy;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $end_date_accuracy;

    private $first_actor = false;

    private $actor1Text;
    private $actor2Text;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActor1(): ?Actor
    {
        return $this->actor_1;
    }

    public function setActor1(?Actor $actor_1): self
    {
        $this->actor_1 = $actor_1;

        return $this;
    }

    public function getActor2(): ?Actor
    {
        return $this->actor_2;
    }

    public function setActor2(?Actor $actor_2): self
    {
        $this->actor_2 = $actor_2;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTextStartDate($get_raw = false)
    {
        if(!is_null($this->text_start_date)||$get_raw) {
            echo "Returning raw date of death: ".$this->text_start_date."<br>";
            return $this->text_start_date;
        }
        else
            return $this->generateTextStartDate();
    }

    public function generateTextStartDate(){
        if(!is_null($this->start_date)&&!is_null($this->start_date_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->start_date, $this->start_date_accuracy);
            return $fuzzy_date->getDateString();
        }
        else
            return "";
    }

    public function setTextStartDate($text_start_date)
    {
        if (empty($text_start_date)) {
            $this->text_start_date = '';
        }
        else{
            $this->text_start_date = $text_start_date;
        }
    }

    public function getStartDate(): ?DateTime
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeInterface $date = null): self
    {
        $this->start_date = $date;
        return $this;
    }

    public function setStartDateByString(string $date = null): self
    {
        $fuzzy_date = new FuzzyDate();
        $fuzzy_date->setByDateString($date);
        $this->setStartDate($fuzzy_date->getFullDate());
        $this->setStartDateAccuracy($fuzzy_date->getAccuracy());
        return $this;
    }


    //***********************************//

    /**
     * @return mixed
     */
    public function getTextEndDate($get_raw = false)
    {
        if(!is_null($this->text_end_date)||$get_raw) {
            echo "Returning raw date of death: ".$this->text_end_date."<br>";
            return $this->text_end_date;
        }
        else
            return $this->generateTextEndDate();
    }

    public function generateTextEndDate(){
        if(!is_null($this->end_date)&&!is_null($this->end_date_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->end_date, $this->end_date_accuracy);
            return $fuzzy_date->getDateString();
        }
        else
            return "";
    }

    public function setTextEndDate($text_end_date)
    {
        if (empty($text_end_date)) {
            $this->end_date = '';
        }
        else{
            $this->text_end_date = $text_end_date;
        }
    }

    public function getEndDate(): ?DateTime
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $date = null): self
    {
        $this->end_date = $date;
        return $this;
    }

    public function setEndDateByString(string $date = null): self
    {
        $fuzzy_date = new FuzzyDate();
        $fuzzy_date->setByDateString($date);
        $this->setEndDate($fuzzy_date->getFullDate());
        $this->setEndDateAccuracy($fuzzy_date->getAccuracy());
        return $this;
    }


    public function getStartDateAccuracy(): ?int
    {
        return $this->start_date_accuracy;
    }

    public function setStartDateAccuracy(?int $start_date_accuracy): self
    {
        $this->start_date_accuracy = $start_date_accuracy;

        return $this;
    }

    public function getEndDateAccuracy(): ?int
    {
        return $this->end_date_accuracy;
    }

    public function setEndDateAccuracy(?int $end_date_accuracy): self
    {
        $this->end_date_accuracy = $end_date_accuracy;

        return $this;
    }

    public function getFirstActor()
    {
        return $this->first_actor;
    }

    public function setFirstActor($first_actor)
    {
        $this->first_actor = $first_actor;
    }

    public function getRelatedActor(){
        if(!is_null($this->first_actor)) {
            if ($this->first_actor)
                return $this->actor_2;
            else if (!$this->first_actor)
                return $this->actor_1;
        }
        else
            return false;
    }

    public function getTypeString()
    {
        if ($this->getActor1()->getBirthdate() < $this->getActor2()->getBirthdate()) {
            if ($this->first_actor) {
                //Is actor 1. Actor 2 is younger.
                switch ($this->type) {
                    case 1:
                        if ($this->actor_2->getGender()) {
                            return "daughter";
                        } else {
                            return "son";
                        }
                    case 2:
                        if ($this->actor_2->getGender())
                            return "wife";
                        else
                            return "husband";
                    case 3:
                        if ($this->actor_2->getGender())
                            return "sister";
                        else
                            return "brother";
                    case 4:
                        return "1:st cousin";
                    case 5:
                        return "student";
                    case 6:
                        return "business partner";
                }
            } else {
                //Is actor 2. Actor 1 is older.
                switch ($this->type) {
                    case 1:
                        if ($this->actor_1->getGender()) {
                            return "mother";
                        } else {
                            return "father";
                        }
                    case 2:
                        if ($this->actor_1->getGender())
                            return "wife";
                        else
                            return "husband";
                    case 3:
                        if ($this->actor_1->getGender())
                            return "sister";
                        else
                            return "brother";
                    case 4:
                        return "1:st cousin";
                    case 5:
                        return "student";
                    case 6:
                        return "business partner";
                }
            }
        } else {
            if ($this->first_actor) {
                //Is actor 1. Actor 2 is older.
                switch ($this->type) {
                    case 1:
                        if ($this->actor_2->getGender()) {
                            return "mother";
                        } else {
                            return "father";
                        }
                    case 2:
                        if ($this->actor_2->getGender())
                            return "wife";
                        else
                            return "husband";
                    case 3:
                        if ($this->actor_2->getGender())
                            return "sister";
                        else
                            return "brother";
                    case 4:
                        return "1:st cousin";
                    case 5:
                        return "teacher";
                    case 6:
                        return "business partner";
                }
            } else {
                //Is actor 2. Actor 1 is younger.
                switch ($this->type) {
                    case 1:
                        if ($this->actor_1->getGender()) {
                            return "daughter";
                        } else {
                            return "son";
                        }
                    case 2:
                        if ($this->actor_1->getGender())
                            return "wife";
                        else
                            return "husband";
                    case 3:
                        if ($this->actor_1->getGender())
                            return "sister";
                        else
                            return "brother";
                    case 4:
                        return "1:st cousin";
                    case 5:
                        return "teacher";
                    case 6:
                        return "business partner";
                }
            }
        }
        return false;
    }

    public function getActor2Text(){
        return $this->getActor2()->getFirstName()." ".$this->getActor2()->getSurname();
    }

    public function setActor2Text($actor2Text){
        $this->actor2Text = $actor2Text;
    }

    public function getActor1Text(){
        return $this->getActor1()->getFirstName()." ".$this->getActor1()->getSurname();
    }

    public function setActor1Text($actor1Text){
        $this->actor1Text = $actor1Text;
    }

}