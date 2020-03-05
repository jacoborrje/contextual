<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActorPlaceRepository")
 * @ORM\Table(name="actors_to_places")
 *
 */
class ActorPlace
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Actor", inversedBy="places")
     * @ORM\JoinColumn(nullable=false)
     */
    private $actor;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="actors")
     * @ORM\JoinColumn(nullable=false)
     */
    private $place;

    /**
     * @ORM\Column(type="date")
     */
    private $date_of_arrival;

    private $text_date_of_arrival;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_of_leaving;

    private $text_date_of_leaving;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $information;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $date_of_arrival_accuracy;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $date_of_leaving_accuracy;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $type;

    private $placeText;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActor(): ?Actor
    {
        return $this->actor;
    }

    public function setActor(?Actor $actor): self
    {
        $this->actor = $actor;

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getDateOfArrival(): ?\DateTimeInterface
    {
        return $this->date_of_arrival;
    }

    public function setDateOfArrival(?\DateTimeInterface $dateOfArrival): self
    {
        $this->date_of_arrival = $dateOfArrival;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTextDateOfArrival()
    {
        if(!is_null($this->text_date_of_arrival))
            return $this->text_date_of_arrival;
        else if(!is_null($this->date_of_arrival)&&!is_null($this->date_of_arrival_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->date_of_arrival, $this->date_of_arrival_accuracy);
            return $fuzzy_date->getDateString();
        }
        else
            return null;    }

    /**
     * @param mixed $text_start_date
     */
    public function setTextDateOfArrival($text_date_of_arrival)
    {
        $this->text_date_of_arrival = $text_date_of_arrival;

    }

    public function setDateOfArrivalByString(string $date = null): self
    {
        if (!is_null($date)) {
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateString($date);
            $this->setDateOfArrival($fuzzy_date->getFullDate());
            $this->setDateOfArrivalAccuracy($fuzzy_date->getAccuracy());
        }
        return $this;
    }

    public function getDateOfLeaving(): ?\DateTimeInterface
    {
        return $this->date_of_leaving;
    }

    public function setDateOfLeaving(?\DateTimeInterface $dateOfLeaving): self
    {
        $this->date_of_leaving = $dateOfLeaving;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTextDateOfLeaving()
    {
        if(!is_null($this->text_date_of_leaving))
            return $this->text_date_of_leaving;
        else if(!is_null($this->date_of_leaving)&&!is_null($this->date_of_leaving_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->date_of_leaving, $this->date_of_leaving_accuracy);
            return $fuzzy_date->getDateString();
        }
        else
            return null;
    }

    public function setDateOfLeavingByString(string $date = null): self
    {
        if (!is_null($date)) {
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateString($date);
            $this->setDateOfLeaving($fuzzy_date->getFullDate());
            $this->setDateOfLeavingAccuracy($fuzzy_date->getAccuracy());
        }
        return $this;
    }

    /**
     * @param mixed $text_date_of_leaving
     */
    public function setTextDateOfLeaving($text_date_of_leaving)
    {
        $this->text_date_of_leaving = $text_date_of_leaving;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(string $information): self
    {
        $this->information = $information;

        return $this;
    }

    public function getDateOfArrivalAccuracy(): ?int
    {
        return $this->date_of_arrival_accuracy;
    }

    public function setDateOfArrivalAccuracy(?int $date_of_arrival_accuracy): self
    {
        $this->date_of_arrival_accuracy = $date_of_arrival_accuracy;

        return $this;
    }

    public function getDateOfLeavingAccuracy(): ?int
    {
        return $this->date_of_leaving_accuracy;
    }

    public function setDateOfLeavingAccuracy(?int $date_of_leaving_accuracy): self
    {
        $this->date_of_leaving_accuracy = $date_of_leaving_accuracy;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPlaceText(): ?string
    {
        if(!is_null($this->getPlace())){
            return $this->getPlace()->getName();
        }
        else{
            return "";
        }
    }

    public function setPlaceText(?string $placeText){
        $this->placeText = $placeText;
    }
}
