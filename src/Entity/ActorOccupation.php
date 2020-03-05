<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActorOccupationRepository")
 * @ORM\Table(name="actors_to_occupations")
 */
class ActorOccupation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Actor", inversedBy="occupations")
     */
    private $actor;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $start_date;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $end_date;

    private $text_start_date;
    private $text_end_date;


    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $start_date_accuracy;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $end_date_accuracy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Institution", inversedBy="members")
     */
    private $institution;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Occupation", inversedBy="actorsWithOccupation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $occupation;

    private $occupationText;
    private $institutionText;

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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(?\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTextStartDate()
    {
        if(!is_null($this->text_start_date))
            return $this->text_start_date;
        else if(!is_null($this->start_date)&&!is_null($this->start_date_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->start_date, $this->start_date_accuracy);
            return $fuzzy_date->getDateString();
        }
        else
            return null;    }

    /**
     * @param mixed $text_start_date
     */
    public function setTextStartDate($text_start_date)
    {
        $this->text_start_date = $text_start_date;

    }

    public function setStartDateByString(string $date = null): self
    {
        if (!is_null($date)) {
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateString($date);
            $this->setStartDate($fuzzy_date->getFullDate());
            $this->setStartDateAccuracy($fuzzy_date->getAccuracy());
        }
        return $this;
    }

    public function setEndDateByString(string $date = null): self
    {
        if (!is_null($date)) {
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateString($date);
            $this->setEndDate($fuzzy_date->getFullDate());
            $this->setEndDateAccuracy($fuzzy_date->getAccuracy());
        }
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(?\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTextEndDate()
    {
        if(!is_null($this->text_end_date))
            return $this->text_end_date;
        else if(!is_null($this->end_date)&&!is_null($this->end_date_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->end_date, $this->end_date_accuracy);
            return $fuzzy_date->getDateString();
        }
        else
            return null;
    }

    /**
     * @param mixed $text_end_date
     */
    public function setTextEndDate($text_end_date)
    {
        $this->text_end_date = $text_end_date;
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

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): self
    {
        $this->institution = $institution;
        return $this;
    }

    public function getOccupation(): ?Occupation
    {
        return $this->occupation;
    }

    public function setOccupation(?Occupation $occupation): self
    {
        $this->occupation = $occupation;
        return $this;
    }

    public function getOccupationText(){
        if(!is_null($this->occupation))
            return $this->getOccupation()->getName();
        else
            return "";
}

    public function setOccupationText($occupationText){
        $this->occupationText = $occupationText;
    }

    public function getInstitutionText(){
        if(!is_null($this->institution))
            return $this->getInstitution()->getName();
        else
            return "";
    }

    public function setInstitutionText($institutionText){
        $this->institutionText = $institutionText;
    }
}
