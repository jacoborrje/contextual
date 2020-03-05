<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Utils\GeographyHelper;
use \DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlaceRepository")
 * @ORM\Table(name="places")
 */
class Place
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Place", mappedBy="parent")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $children;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $lat;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $lng;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Institution", mappedBy="place")
     */
    private $institutions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Actor", mappedBy="birth_place")
     */
    private $births;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Actor", mappedBy="place_of_death")
     */
    private $deaths;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Action", mappedBy="place")
     */
    private $actions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Archive", mappedBy="place")
     */
    private $archives;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mention", mappedBy="place")
     */
    private $mentions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ActorPlace", mappedBy="place", orphanRemoval=true)
     */
    private $actors;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $alt_names;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $alvin_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MapOverlay", mappedBy="place")
     */
    private $mapOverlays;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DatabaseFile", inversedBy="places", cascade={"persist"})
     */
    private $image;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mention", mappedBy="EventPlace")
     */
    private $EventMentions;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->institutions = new ArrayCollection();
        $this->births = new ArrayCollection();
        $this->deaths = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->archives = new ArrayCollection();
        $this->mentions = new ArrayCollection();
        $this->actors = new ArrayCollection();
        $this->mapOverlays = new ArrayCollection();
        $this->EventMentions = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function __toInteger(){
        return $this->id;
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



    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function hasParent(): ?bool
    {
        if(!is_null($this->getParent())){
            return true;
        }
        else
            return false;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getDecendants(){
        $decendants = [];
        foreach($this->getChildren() as $child){
            $decendants[] = $child;
            if(count($child->getChildren())>0){
                $decendants = array_merge($decendants, $child->getDecendants());
            }
        }
        return $decendants;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?float
    {
        return $this->lng;
    }

    public function setLng(float $lng): self
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * @return Collection|Institution[]
     */
    public function getInstitutions(): Collection
    {
        return $this->institutions;
    }

    public function addInstitution(Institution $institution): self
    {
        if (!$this->institutions->contains($institution)) {
            $this->institutions[] = $institution;
            $institution->setPlace($this);
        }

        return $this;
    }

    public function removeInstitution(Institution $institution): self
    {
        if ($this->institutions->contains($institution)) {
            $this->institutions->removeElement($institution);
            // set the owning side to null (unless already changed)
            if ($institution->getPlace() === $this) {
                $institution->setPlace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Actor[]
     */
    public function getBirths(): Collection
    {
        return $this->births;
    }

    public function addBirth(Actor $birth): self
    {
        if (!$this->births->contains($birth)) {
            $this->births[] = $birth;
            $birth->setBirthPlace($this);
        }

        return $this;
    }

    public function removeBirth(Actor $birth): self
    {
        if ($this->births->contains($birth)) {
            $this->births->removeElement($birth);
            // set the owning side to null (unless already changed)
            if ($birth->getBirthPlace() === $this) {
                $birth->setBirthPlace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Actor[]
     */
    public function getDeaths(): Collection
    {
        return $this->deaths;
    }

    public function addDeath(Actor $death): self
    {
        if (!$this->deaths->contains($death)) {
            $this->deaths[] = $death;
            $death->setPlaceOfDeath($this);
        }

        return $this;
    }

    public function removeDeath(Actor $death): self
    {
        if ($this->deaths->contains($death)) {
            $this->deaths->removeElement($death);
            // set the owning side to null (unless already changed)
            if ($death->getPlaceOfDeath() === $this) {
                $death->setPlaceOfDeath(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Action[]
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(Action $action): self
    {
        if (!$this->actions->contains($action)) {
            $this->actions[] = $action;
            $action->setPlace($this);
        }

        return $this;
    }

    public function removeAction(Action $action): self
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
            // set the owning side to null (unless already changed)
            if ($action->getPlace() === $this) {
                $action->setPlace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Archive[]
     */
    public function getArchives(): Collection
    {
        return $this->archives;
    }

    public function addArchive(Archive $archive): self
    {
        if (!$this->archives->contains($archive)) {
            $this->archives[] = $archive;
            $archive->setPlace($this);
        }

        return $this;
    }

    public function removeArchive(Archive $archive): self
    {
        if ($this->archives->contains($archive)) {
            $this->archives->removeElement($archive);
            // set the owning side to null (unless already changed)
            if ($archive->getPlace() === $this) {
                $archive->setPlace(null);
            }
        }

        return $this;
    }

    public function setGeoData(){
        $geo_helper = new GeographyHelper();

        if(is_null($this->getParent())){
            $geo_data = $geo_helper->geocodeAddress($this->name);
        }
        else{
            $geo_data = $geo_helper->geocodeAddress($this->name,$this->getParent()->getName());
        }
        $this->setLat($geo_data['lat']);
        $this->setLng($geo_data['lon']);
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
            $mention->setPlace($this);
        }

        return $this;
    }

    public function removeMention(Mention $mention): self
    {
        if ($this->mentions->contains($mention)) {
            $this->mentions->removeElement($mention);
            // set the owning side to null (unless already changed)
            if ($mention->getPlace() === $this) {
                $mention->setPlace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ActorPlace[]
     */
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(ActorPlace $actor): self
    {
        if (!$this->actors->contains($actor)) {
            $this->actors[] = $actor;
            $actor->setPlace($this);
        }

        return $this;
    }

    public function removeActor(ActorPlace $actor): self
    {
        if ($this->actors->contains($actor)) {
            $this->actors->removeElement($actor);
            // set the owning side to null (unless already changed)
            if ($actor->getPlace() === $this) {
                $actor->setPlace(null);
            }
        }

        return $this;
    }

    public function getAltNames(): ?string
    {
        return $this->alt_names;
    }

    public function hasAltNames(){
        if(!is_null($this->getAltNames())&&strlen($this->getAltNames())>0)
            return true;
        else
            return false;
    }

    public function getAltNamesArray()
    {
        if(strpos($this->alt_names, ", ")!==false) {
            $returnArray = array_map('trim', explode(', ', $this->alt_names));
            return $returnArray;
        }
        else
            return [trim($this->alt_names)];
    }

    public function setAltNames(?string $alt_names): self
    {
        $this->alt_names = $alt_names;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type = null): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeString(){
        switch ($this->type) {
            case 1: return "Country";
            case 2: return "City";
            case 3: return "Town";
            case 4: return "Village";
            case 5: return "Hamlet";
            case 6: return "Neighbourhood";
            case 7: return "Region";
            case 8: return "Street";
            case 9: return "House";
            case 10: return "Church";
            case 11: return "Castle";
            case 12: return "Estate";
            case 13: return "Suburb";
        }
        return false;
    }

    /**
     * @return Collection|MapOverlay[]
     */
    public function getMapOverlays(): Collection
    {
        if(count($this->mapOverlays)===0){
            return $this->parent->getMapOverlays();
        }
        else
            return $this->mapOverlays;
    }

    public function addMapOverlay(MapOverlay $mapOverlay): self
    {
        if (!$this->mapOverlays->contains($mapOverlay)) {
            $this->mapOverlays[] = $mapOverlay;
            $mapOverlay->setPlace($this);
        }

        return $this;
    }

    public function removeMapOverlay(MapOverlay $mapOverlay): self
    {
        if ($this->mapOverlays->contains($mapOverlay)) {
            $this->mapOverlays->removeElement($mapOverlay);
            // set the owning side to null (unless already changed)
            if ($mapOverlay->getPlace() === $this) {
                $mapOverlay->setPlace(null);
            }
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

    public function getImage(): ?DatabaseFile
    {
        return $this->image;
    }

    public function setImage(?DatabaseFile $image): self
    {
        $this->image = $image;
        return $this;
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

    /**
     * @return Collection|Mention[]
     */
    public function getEventMentions(): Collection
    {
        return $this->EventMentions;
    }

    public function addEventMention(Mention $eventMention): self
    {
        if (!$this->EventMentions->contains($eventMention)) {
            $this->EventMentions[] = $eventMention;
            $eventMention->setEventPlace($this);
        }

        return $this;
    }

    public function removeEventMention(Mention $eventMention): self
    {
        if ($this->EventMentions->contains($eventMention)) {
            $this->EventMentions->removeElement($eventMention);
            // set the owning side to null (unless already changed)
            if ($eventMention->getEventPlace() === $this) {
                $eventMention->setEventPlace(null);
            }
        }

        return $this;
    }
}