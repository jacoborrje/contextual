<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActionRepository")
 * @ORM\Table(name="actions")
 */
class Action
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Correspondent", inversedBy="actions", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $correspondent;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $start_date;

    private $text_start_date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $start_date_accuracy;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $end_date;

    private $text_end_date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $end_date_accuracy;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $research_notes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Source", inversedBy="actions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $source;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="actions")
     */
    private $place;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mention", mappedBy="action")
     */
    private $mentions;

    private $correspondentText;
    private $placeText;

    public function __construct()
    {
        $this->mentions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }



    public function __clone() {
        if ($this->id) {
            $this->setId(null);
        }
    }

    public function getCorrespondent(): ?Correspondent
    {
        return $this->correspondent;
    }

    public function setCorrespondent(?Correspondent $correspondent): self
    {
        $this->correspondent = $correspondent;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        if(!is_null($this->start_date)) {
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->start_date, $this->start_date_accuracy);
            return $fuzzy_date->getDateString();
        }
        else
            return null;
    }

    public function setStartDate(\DateTimeInterface $start_date = null): self
    {
        $this->start_date = $start_date;

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

    public function getEndDate(): ?\DateTimeInterface
    {
        if(!is_null($this->end_date)) {
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->end_date, $this->end_date_accuracy);
            return $fuzzy_date->getDateString();
        }
        else
            return null;
    }

    public function setEndDate(?\DateTimeInterface $end_date = null): self
    {
        $this->end_date = $end_date;

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
            return "undated";
    }

    /**
     * @param mixed $text_start_date
     */
    public function setTextStartDate($text_start_date)
    {
        if(is_null($text_start_date)){
            $this->text_start_date = "";
        }
        else {
            $this->text_start_date = $text_start_date;
        }
    }

    /**
     * @return mixed
     */
    public function getTextEndDate()
    {
        if(!is_null($this->end_date)&&!is_null($this->end_date_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->end_date, $this->end_date_accuracy);
            return $fuzzy_date->getDateString();
        }
        else if(!is_null($this->text_end_date))
            return $this->text_end_date;
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeNoun(): string
    {
        switch ($this->type) {
            case 0:
                return "void";
            case 1:
                return "author";
            case 2:
                return "receiver";
            case 3:
                return "signer";
            case 4:
                return "deliverer";
            case 5:
                return "answerer";
        }
    }

    public function getTypeVerbImperfect(): string
    {
        switch ($this->type) {
            case 0:
                return "void";
            case 1:
                return "authored";
            case 2:
                return "received";
            case 3:
                return "signed";
            case 4:
                return "delivered";
            case 5:
                return "answered";
        }
    }




    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getResearchNotes(): ?string
    {
        return $this->research_notes;
    }

    public function setResearchNotes(?string $research_notes): self
    {
        $this->research_notes = $research_notes;

        return $this;
    }

    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function setSource(?Source $source): self
    {
        $this->source = $source;

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

    /**
     * @return Collection|Mention[]
     */
    public function getMentions(): Collection
    {
        return $this->mentions;
    }

    public function addMention(Mention $mention): self
    {
        if (!$this->mentions->contains($mention)) {
            $this->mentions[] = $mention;
            $mention->setAction($this);
        }

        return $this;
    }

    public function removeMention(Mention $mention): self
    {
        if ($this->mentions->contains($mention)) {
            $this->mentions->removeElement($mention);
            // set the owning side to null (unless already changed)
            if ($mention->getAction() === $this) {
                $mention->setAction(null);
            }
        }

        return $this;
    }

    public function getRawStartDate(){
        return $this->start_date;
    }

    public function getRawEndDate(){
        return $this->end_date;
    }

    public function getCorrespondentText(){
        if(!is_null($this->getCorrespondent())) {
            if (!is_null($this->getCorrespondent()->getActor())) {
                return $this->getCorrespondent()->getActor()->getFirstName() . " " . $this->getCorrespondent()->getActor()->getSurname();
            } elseif (!is_null($this->getCorrespondent()->getInstitution())) {
                return $this->getCorrespondent()->getInstitution()->getName();
            } else {
                return "";
            }
        }
        else{
            return null;
        }
    }

    public function setCorrespondentText($correspondentText){
        $this->correspondentText = $correspondentText;
    }

    public function getPlaceText(){
        if(!is_null($this->getPlace())){
            return $this->getPlace()->getName();
        }
        else
            return "";
    }

    public function setPlaceText($placeText){
        $this->placeText = $placeText;
    }


}
