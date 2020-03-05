<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Object_;
use \DateTime;
use PhpParser\Node\Scalar\String_;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActorRepository")
 * @ORM\Table(name="actors")
 */
class Actor
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $surname;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $first_name;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthdate;

    private $text_birthdate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $birthdate_accuracy;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_of_death;

    private $text_date_of_death;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $date_of_death_accuracy;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $research_notes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="births", cascade={"persist"})
     */
    private $birth_place;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="deaths", cascade={"persist"})
     */
    private $place_of_death;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $gender;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Relationship", mappedBy="actor_1", orphanRemoval=true, cascade={"persist"})
     */
    private $primaryRelationships;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Relationship", mappedBy="actor_2", orphanRemoval=true, cascade={"persist"})
     */
    private $secondaryRelationships;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ActorOccupation", mappedBy="actor", orphanRemoval=true, cascade={"persist"})
     */
    private $occupations;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Correspondent", mappedBy="actor", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $correspondent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mention", mappedBy="actor")
     */
    private $mentions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ActorPlace", mappedBy="actor", orphanRemoval=true, cascade={"persist"})
     */
    private $places;

    /**
     * @ORM\Column(type="string", length=2048, nullable=true)
     */
    private $alt_surnames;

    /**
     * @ORM\Column(type="string", length=2048, nullable=true)
     */
    private $alt_first_names;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $alvin_id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    private $birthPlaceText;
    private $placeOfDeathText;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mention", mappedBy="secondaryActor")
     */
    private $secondaryMentions;

    public function __construct()
    {
        $this->relationships = new ArrayCollection();
        $this->primaryRelationships = new ArrayCollection();
        $this->secondaryRelationships = new ArrayCollection();
        $this->occupations = new ArrayCollection();
        $this->mentions = new ArrayCollection();
        $this->places = new ArrayCollection();
        $this->secondaryMentions = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->first_name ." ". $this->surname;
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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getBirthdate(): ?DateTime
    {
        return $this->birthdate;
    }

    public function getBirthYear(): ?String
    {
        if(!is_null($this->birthdate)) {
            return $this->birthdate->format("Y");
        }
        else{
            return null;
        }
    }

    public function getDeathYear(): ?String
    {
        if(!is_null($this->date_of_death)) {
            return $this->date_of_death->format("Y");
        }
        else{
            return null;
        }
    }

    public function setBirthdate(\DateTimeInterface $date = null): self
    {
        $this->birthdate = $date;
        return $this;
    }

    public function setBirthdateByString(string $date = null): self
    {
        $fuzzy_date = new FuzzyDate();
        $fuzzy_date->setByDateString($date);
        $this->setBirthdate($fuzzy_date->getFullDate());
        $this->setBirthdateAccuracy($fuzzy_date->getAccuracy());
        return $this;
    }
    public function setDateOfDeathByString(string $date = null): self
    {
        $fuzzy_date = new FuzzyDate();
        $fuzzy_date->setByDateString($date);
        $this->setDateOfDeath($fuzzy_date->getFullDate());
        $this->setDateOfDeathAccuracy($fuzzy_date->getAccuracy());
        return $this;
    }


    public function getBirthdateAccuracy(): ?int
    {
        return $this->birthdate_accuracy;
    }

    public function setBirthdateAccuracy(?int $birthdate_accuracy): self
    {
        $this->birthdate_accuracy = $birthdate_accuracy;

        return $this;
    }

    public function getDateOfDeath(): ?DateTime
    {
        return $this->date_of_death;
    }

    /**
     * @return mixed
     */
    public function getTextBirthdate($get_raw = false)
    {
        if(!is_null($this->text_birthdate)||$get_raw) {
            echo "Returning raw birthdate: ".$this->text_birthdate."<br>";
            return $this->text_birthdate;
        }
        else
            return $this->generateTextBirthdate();
    }

    public function generateTextBirthdate()
    {
        if(!is_null($this->birthdate)&&!is_null($this->birthdate_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->birthdate, $this->birthdate_accuracy);
            return $fuzzy_date->getDateString();
        }
        else {
            return "";
        }
    }

    /**
     * @param mixed $text_birthdate
     */
    public function setTextBirthdate($text_birthdate)
    {
        if (empty($text_birthdate)) {
            $this->text_birthdate = '';
        }
        else{
            $this->text_birthdate = $text_birthdate;
        }


    }

    /**
     * @return mixed
     */
    public function getTextDateOfDeath($get_raw = false)
    {
        if(!is_null($this->text_date_of_death)||$get_raw) {
            echo "Returning raw date of death: ".$this->text_date_of_death."<br>";
            return $this->text_date_of_death;
        }
        else
            return $this->generateTextDateOfDeath();
    }

    public function generateTextDateOfDeath(){
        if(!is_null($this->date_of_death)&&!is_null($this->date_of_death_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->date_of_death, $this->date_of_death_accuracy);
            return $fuzzy_date->getDateString();
        }
        else
            return "";
    }

    /**
     * @param mixed $text_date_of_death
     */
    public function setTextDateOfDeath($text_date_of_death)
    {
        if (empty($text_date_of_death)) {
            $this->text_date_of_death = '';
        }
        else{
            $this->text_date_of_death = $text_date_of_death;
        }

    }


    public function setDateOfDeath(\DateTimeInterface $date = null): self
    {
        $this->date_of_death = $date;
        return $this;
    }



    public function getDateOfDeathAccuracy(): ?int
    {
        return $this->date_of_death_accuracy;
    }

    public function setDateOfDeathAccuracy(?int $date_of_death_accuracy): self
    {
        $this->date_of_death_accuracy = $date_of_death_accuracy;

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

    public function getResearchNotes(): ?string
    {
        return $this->research_notes;
    }

    public function setResearchNotes(?string $research_notes): self
    {
        $this->research_notes = $research_notes;

        return $this;
    }

    public function getBirthPlace(): ?Place
    {
        return $this->birth_place;
    }

    public function setBirthPlace(?Place $birth_place): self
    {
        $this->birth_place = $birth_place;

        return $this;
    }

    public function getPlaceOfDeath(): ?Place
    {
        return $this->place_of_death;
    }

    public function setPlaceOfDeath(?Place $place_of_death): self
    {
        $this->place_of_death = $place_of_death;

        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(int $gender = null): self
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return Collection|Relationship[]
     */
    public function getPrimaryRelationships(): Collection
    {
        $relationships = $this->primaryRelationships;
        foreach($relationships as $relationship){
            $relationship->setFirstActor(true);
        }
        return $relationships;
    }

    public function addPrimaryRelationship(Relationship $primaryRelationship): self
    {
        if (!$this->primaryRelationships->contains($primaryRelationship)) {
            $this->primaryRelationships[] = $primaryRelationship;
            $primaryRelationship->setActor1($this);
        }

        return $this;
    }

    public function removePrimaryRelationship(Relationship $primaryRelationship): self
    {
        if ($this->primaryRelationships->contains($primaryRelationship)) {
            $this->primaryRelationships->removeElement($primaryRelationship);
            // set the owning side to null (unless already changed)
            if ($primaryRelationship->getActor1() === $this) {
                $primaryRelationship->setActor1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Relationship[]
     */
    public function getSecondaryRelationships(): Collection
    {
        $relationships = $this->secondaryRelationships;
        foreach($relationships as $relationship){
            $relationship->setFirstActor(false);
        }
        return $relationships;
    }

    public function addSecondaryRelationship(Relationship $secondaryRelationship): self
    {
        if (!$this->secondaryRelationships->contains($secondaryRelationship)) {
            $this->secondaryRelationships[] = $secondaryRelationship;
            $secondaryRelationship->setActor2($this);
        }

        return $this;
    }

    public function removeSecondaryRelationship(Relationship $secondaryRelationship): self
    {
        if ($this->secondaryRelationships->contains($secondaryRelationship)) {
            $this->secondaryRelationships->removeElement($secondaryRelationship);
            // set the owning side to null (unless already changed)
            if ($secondaryRelationship->getActor2() === $this) {
                $secondaryRelationship->setActor2(null);
            }
        }

        return $this;
    }

    public function getRelationships(): Collection{
        $allRelationships =  new ArrayCollection(
            array_merge($this->getPrimaryRelationships()->toArray(), $this->getSecondaryRelationships()->toArray())
            );
        return $allRelationships;

    }

    /**
     * @return Collection|ActorOccupation[]
     */
    public function getOccupations(): Collection
    {
        return $this->occupations;
    }

    public function addOccupation(ActorOccupation $occupation): self
    {
        if (!$this->occupations->contains($occupation)) {
            $this->occupations[] = $occupation;
            $occupation->setActor($this);
        }

        return $this;
    }

    public function removeOccupation(ActorOccupation $occupation): self
    {
        if ($this->occupations->contains($occupation)) {
            $this->occupations->removeElement($occupation);
            // set the owning side to null (unless already changed)
            if ($occupation->getActor() === $this) {
                $occupation->setActor(null);
            }
        }
        return $this;
    }

    public function getCorrespondent(): ?Correspondent
    {
        return $this->correspondent;
    }

    public function hasCorrespondent(){
        if (is_null($this->getCorrespondent()))
            return false;
        else
            return true;
    }

    public function getActions(): ?Collection
    {
        return $this->correspondent->getActions();
    }

    public function setCorrespondent(?Correspondent $correspondent): self
    {
        $this->correspondent = $correspondent;

        // set (or unset) the owning side of the relation if necessary
        $newActor = $correspondent === null ? null : $this;
        if ($newActor !== $correspondent->getActor()) {
            $correspondent->setActor($newActor);
        }

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
            $mention->setActor($this);
        }

        return $this;
    }

    public function removeMention(Mention $mention): self
    {
        if ($this->mentions->contains($mention)) {
            $this->mentions->removeElement($mention);
            // set the owning side to null (unless already changed)
            if ($mention->getActor() === $this) {
                $mention->setActor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ActorPlace[]
     */
    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function addPlace(ActorPlace $place): self
    {
        if (!$this->places->contains($place)) {
            $this->places[] = $place;
            $place->setActor($this);
        }

        return $this;
    }

    public function removePlace(ActorPlace $place): self
    {
        if ($this->places->contains($place)) {
            $this->places->removeElement($place);
            // set the owning side to null (unless already changed)
            if ($place->getActor() === $this) {
                $place->setActor(null);
            }
        }

        return $this;
    }

    public function getAltSurnames(): ?string
    {
        return $this->alt_surnames;
    }

    public function getAltSurnamesArray()
    {
        if(strpos($this->alt_surnames, ", ")!==false)
            return array_map('trim', explode(', ', $this->alt_surnames));
        else
            return [trim($this->alt_surnames)];
    }

    public function hasAltSurnames(){
        if(!is_null($this->getAltSurnames())&&strlen($this->getAltSurnames())>0)
            return true;
        else
            return false;
    }

    public function setAltSurnames(?string $alt_surnames): self
    {
        $this->alt_surnames = $alt_surnames;

        return $this;
    }

    public function getAltFirstNames(): ?string
    {
        return $this->alt_first_names;
    }

    public function getAltFirstNamesArray()
    {
        if(strpos($this->alt_first_names, ", ")!==false)
            return array_map('trim', explode(', ', $this->alt_first_names));
        else
            return [trim($this->alt_first_names)];
    }

    public function hasAltFirstNames(){
        if(!is_null($this->getAltFirstNames())&&strlen($this->getAltFirstNames())>0) {
            return true;
        }
        else
            return false;
    }


    public function setAltFirstNames(?string $alt_first_names): self
    {
        $this->alt_first_names = $alt_first_names;

        return $this;
    }

    public function getAlvinId(): ?int
    {
        return $this->alvin_id;
    }

    public function setAlvinId(?int $alvin_id): self
    {
        $this->alvin_id = $alvin_id;

        return $this;
    }

    public function getPossessivePronoun(){
        if($this->gender)
            return "her";
        else
            return "his";
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at = null): self
    {
        if(is_null($updated_at)){
            $currentTime = new DateTime();
            $this->updated_at = $currentTime;
        }
        else {
            $this->updated_at = $updated_at;
        }
        return $this;
    }

    public function getBirthPlaceText(){
        if(!is_null($this->getBirthPlace())) {
            return $this->getBirthPlace()->getName();
        }
        else
            return "";
    }

    public function setBirthPlaceText($birthPlaceText){
        $this->birthPlaceText = $birthPlaceText;
    }

    public function getPlaceOfDeathText(){
        if(!is_null($this->getPlaceOfDeath())){
            return $this->getPlaceOfDeath()->getName();
        }
        else
            return "";
    }

    public function setPlaceOfDeathText($placeOfDeath){
        $this->place_of_death = $placeOfDeath;
    }

    /**
     * @return Collection|Mention[]
     */
    public function getSecondaryMentions(): Collection
    {
        return $this->secondaryMentions;
    }

    public function addSecondaryMention(Mention $secondaryMention): self
    {
        if (!$this->secondaryMentions->contains($secondaryMention)) {
            $this->secondaryMentions[] = $secondaryMention;
            $secondaryMention->setSecondaryActor($this);
        }

        return $this;
    }

    public function removeSecondaryMention(Mention $secondaryMention): self
    {
        if ($this->secondaryMentions->contains($secondaryMention)) {
            $this->secondaryMentions->removeElement($secondaryMention);
            // set the owning side to null (unless already changed)
            if ($secondaryMention->getSecondaryActor() === $this) {
                $secondaryMention->setSecondaryActor(null);
            }
        }

        return $this;
    }

}