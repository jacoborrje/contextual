<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InstitutionRepository")
 * @ORM\Table(name="institutions")
 */
class Institution
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
    private $name;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_of_establishment;
    private $text_date_of_establishment;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $date_of_establishment_accuracy;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_of_dissolution;
    private $text_date_of_dissolution;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $date_of_dissolution_accuracy;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $research_notes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="institutions", cascade={"persist"})
     */
    private $place;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ActorOccupation", mappedBy="institution")
     */
    private $members;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Correspondent", mappedBy="institution", cascade={"persist", "remove"})
     */
    private $correspondent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mention", mappedBy="institution")
     */
    private $mentions;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Archive", inversedBy="institutions")
     */
    private $archive;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->mentions = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateOfEstablishment(): ?\DateTimeInterface
    {
        return $this->date_of_establishment;
    }

    public function setDateOfEstablishment(?\DateTimeInterface $date_of_establishment): self
    {
        $this->date_of_establishment = $date_of_establishment;

        return $this;
    }

    public function getDateOfEstablishmentAccuracy(): ?int
    {
        return $this->date_of_establishment_accuracy;
    }

    public function setDateOfEstablishmentAccuracy(?int $date_of_establishment_accuracy): self
    {
        $this->date_of_establishment_accuracy = $date_of_establishment_accuracy;

        return $this;
    }

    public function setDateOfEstablishmentByString(string $date = null): self
    {
        if(!is_null($date)) {
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateString($date);
            $this->setDateOfEstablishment($fuzzy_date->getFullDate());
            $this->setDateOfEstablishmentAccuracy($fuzzy_date->getAccuracy());
        }
        return $this;
    }

    public function setDateOfDissolutionByString(string $date = null): self
    {
        if(!is_null($date)) {
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateString($date);
            $this->setDateOfDissolution($fuzzy_date->getFullDate());
            $this->setDateOfDissolutionAccuracy($fuzzy_date->getAccuracy());
        }
        return $this;
    }

    public function getDateOfDissolution(): ?\DateTimeInterface
    {
        return $this->date_of_dissolution;
    }

    public function setDateOfDissolution(?\DateTimeInterface $date_of_dissolution): self
    {
        $this->date_of_dissolution = $date_of_dissolution;

        return $this;
    }

    public function getDateOfDissolutionAccuracy(): ?int
    {
        return $this->date_of_dissolution_accuracy;
    }

    public function setDateOfDissolutionAccuracy(?int $date_of_dissolution_accuracy): self
    {
        $this->date_of_dissolution_accuracy = $date_of_dissolution_accuracy;

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

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getTextDateOfEstablishment()
    {
        if(!is_null($this->date_of_establishment)&&!is_null($this->date_of_establishment_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->date_of_establishment, $this->date_of_establishment_accuracy);
            return $fuzzy_date->getDateString();
        }
        else if(!is_null($this->text_date_of_establishment))
            return $this->text_date_of_establishment;
        else
            return null;
    }


    /**
     * @param mixed $text_date_of_establishment
     */
    public function setTextDateOfEstablishment($text_date_of_establishment)
    {
        $this->text_date_of_establishment = $text_date_of_establishment;
    }

    /**
     * @return mixed
     */
    public function getTextDateOfDissolution()
    {
        if(!is_null($this->date_of_dissolution)&&!is_null($this->date_of_dissolution_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->date_of_dissolution, $this->date_of_dissolution_accuracy);
            return $fuzzy_date->getDateString();
        }
        else if(!is_null($this->text_date_of_dissolution))
            return $this->text_date_of_dissolution;
        else
            return null;
    }

    /**
     * @param mixed $text_date_of_dissolution
     */
    public function setTextDateOfDissolution($text_date_of_dissolution)
    {
        $this->text_date_of_dissolution = $text_date_of_dissolution;
    }

    /**
     * @return Collection|ActorOccupation[]
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(ActorOccupation $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
            $member->setInstitution($this);
        }

        return $this;
    }

    public function removeMember(ActorOccupation $member): self
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
            // set the owning side to null (unless already changed)
            if ($member->getInstitution() === $this) {
                $member->setInstitution(null);
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
        return $this->getCorrespondent()->getActions();
    }

    public function setCorrespondent(?Correspondent $correspondent): self
    {
        $this->correspondent = $correspondent;

        // set (or unset) the owning side of the relation if necessary
        $newInstitution = $correspondent === null ? null : $this;
        if ($newInstitution !== $correspondent->getInstitution()) {
            $correspondent->setInstitution($newInstitution);
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
            $mention->setInstitution($this);
        }

        return $this;
    }

    public function removeMention(Mention $mention): self
    {
        if ($this->mentions->contains($mention)) {
            $this->mentions->removeElement($mention);
            // set the owning side to null (unless already changed)
            if ($mention->getInstitution() === $this) {
                $mention->setInstitution(null);
            }
        }

        return $this;
    }

    public function getArchive(): ?Archive
    {
        return $this->archive;
    }

    public function setArchive(?Archive $archive): self
    {
        $this->archive = $archive;

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
}
