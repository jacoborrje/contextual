<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MentionRepository")
 * @ORM\Table(name="mentions")
 */
class Mention
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Source", inversedBy="mentions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $info_source;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $date_accuracy;

    private $text_date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Actor", inversedBy="mentions", cascade={"persist"})
     */
    private $actor;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Institution", inversedBy="mentions", cascade={"persist"})
     */
    private $institution;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="mentions")
     */
    private $place;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Action", inversedBy="mentions")
     */
    private $action;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Source", inversedBy="mentions")
     */
    private $mentioned_source;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $start_page;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $end_page;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $start_pos;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $end_pos;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="EventMentions")
     */
    private $EventPlace;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $time;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $verb;

    private $actorText;
    private $placeText;
    private $eventPlaceText;
    private $institutionText;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Actor", inversedBy="secondaryMentions")
     */
    private $secondaryActor;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInfoSource(): ?Source
    {
        return $this->info_source;
    }

    public function setInfoSource(?Source $info_source): self
    {
        $this->info_source = $info_source;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDateAccuracy(): ?int
    {
        return $this->date_accuracy;
    }

    public function setDateAccuracy(?int $date_accuracy): self
    {
        $this->date_accuracy = $date_accuracy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTextDate()
    {
        if(!is_null($this->date)&&!is_null($this->date_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->date, $this->date_accuracy);
            return $fuzzy_date->getDateString();
        }
        else if(!is_null($this->text_date))
            return $this->text_date;
        else
            return null;    }

    /**
     * @param mixed $text_date
     */
    public function setTextDate($text_date)
    {
        $this->text_date = $text_date;
    }

    public function setDateByString(string $date = null): self
    {
        if (!is_null($date)) {
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateString($date);
            $this->setDate($fuzzy_date->getFullDate());
            $this->setDateAccuracy($fuzzy_date->getAccuracy());
        }
        return $this;
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

    public function getActor(): ?Actor
    {
        return $this->actor;
    }

    public function setActor(?Actor $actor): self
    {
        $this->actor = $actor;

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

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(?Action $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getMentionedSource(): ?Source
    {
        return $this->mentioned_source;
    }

    public function setMentionedSource(?Source $mentioned_source): self
    {
        $this->mentioned_source = $mentioned_source;

        return $this;
    }

    public function getStartPage(): ?int
    {
        return $this->start_page;
    }

    public function setStartPage(?int $start_page): self
    {
        $this->start_page = $start_page;

        return $this;
    }

    public function getEndPage(): ?int
    {
        return $this->end_page;
    }

    public function setEndPage(?int $end_page): self
    {
        $this->end_page = $end_page;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartPos()
    {
        return $this->start_pos;
    }

    /**
     * @param mixed $start_pos
     */
    public function setStartPos($start_pos): void
    {
        $this->start_pos = $start_pos;
    }

    /**
     * @return mixed
     */
    public function getEndPos()
    {
        return $this->end_pos;
    }

    /**
     * @param mixed $end_pos
     */
    public function setEndPos($end_pos): void
    {
        $this->end_pos = $end_pos;
    }

    public function getEventPlace(): ?Place
    {
        return $this->EventPlace;
    }

    public function setEventPlace(?Place $EventPlace): self
    {
        $this->EventPlace = $EventPlace;

        return $this;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(?int $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getTextTime(){
        $integerTime = $this->getTime();
        $hours = intdiv($integerTime, 60);
        $minutes = $integerTime % 60;
        if($minutes === 0)
            $minutes = "00";

        return $hours .":".$minutes;
    }

    public function getPreviewText(){
        $previewText = "";

        if(!is_null($this->getTextDate())){
            $previewText .= $this->getTextDate();
        }
        if(!is_null($this->getTextTime())){
            $previewText .= " (".$this->getTextTime() ."), ";
        }

        if(!is_null($this->actor)){
            $previewText .= $this->actor . " ";
        }
        if(!is_null($this->verb)){
            $previewText .= $this->verb . " ";
        }
        if(!is_null($this->institution)){
            $previewText .= $this->institution . " in ";
        }
        if(!is_null($this->getEventPlace())){
            $previewText .= " ".$this->getEventPlace();
        }
        return $previewText;
    }

    public function getVerb(): ?string
    {
        return $this->verb;
    }

    public function setVerb(?string $verb): self
    {
        $this->verb = $verb;

        return $this;
    }

    public function getActorText(){
        if(!is_null($this->getActor())) {
            return $this->getActor()->getFirstName() . " " . $this->getActor()->getSurname();
        }
        else{
            return "";
        }
    }

    public function setActorText($actorText){
        $this->actorText = $actorText;
    }

    public function getPlaceText(){
        if(!is_null($this->getPlace())) {
            return $this->getPlace()->getName();
        }
        else{
            return "";
        }
    }

    public function setPlaceText($placeText){
        $this->placeText = $placeText;
    }


    public function getEventPlaceText(){
        if(!is_null($this->getEventPlace())) {
            return $this->getEventPlace()->getName();
        }
        else{
            return "";
        }
    }

    public function setEventPlaceText($placeText){
        $this->eventPlaceText = $placeText;
    }

    public function getInstitutionText(){
        if(!is_null($this->getInstitution())) {
            return $this->getInstitution()->getName();
        }
        else{
            return "";
        }
    }

    public function setInstitutionText($institutionText){
        $this->institutionText = $institutionText;
    }

    public function getSecondaryActor(): ?Actor
    {
        return $this->secondaryActor;
    }

    public function setSecondaryActor(?Actor $secondaryActor): self
    {
        $this->secondaryActor = $secondaryActor;

        return $this;
    }

}
