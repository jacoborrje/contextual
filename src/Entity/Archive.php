<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use \DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArchiveRepository")
 * @ORM\Table(name="archives")
 */
class Archive
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $research_notes;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $abbreviation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Archive", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Archive", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $start_time;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $start_time_accuracy;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $end_time;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $end_time_accuracy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Series", mappedBy="archive")
     */
    private $series;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="archives")
     * @ORM\JoinColumn(nullable=false)
     */
    private $place;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Institution", mappedBy="archive")
     */
    private $institutions;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    public function __toString()
    {
        return $this->name;
    }

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->series = new ArrayCollection();
        $this->institutions = new ArrayCollection();
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

    public function getResearchNotes(): ?string
    {
        return $this->research_notes;
    }

    public function setResearchNotes(?string $research_notes): self
    {
        $this->research_notes = $research_notes;

        return $this;
    }

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(?string $abbreviation): self
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
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

    public function hasChildren(): Boolean
    {
        return is_null($this->getChildren());
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

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->start_time;
    }

    public function setStartTime(?\DateTimeInterface $start_time): self
    {
        $this->start_time = $start_time;

        return $this;
    }

    public function getStartTimeAccuracy(): ?int
    {
        return $this->start_time_accuracy;
    }

    public function setStartTimeAccuracy(?int $start_time_accuracy): self
    {
        $this->start_time_accuracy = $start_time_accuracy;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->end_time;
    }

    public function setEndTime(?\DateTimeInterface $end_time): self
    {
        $this->end_time = $end_time;

        return $this;
    }

    public function getEndTimeAccuracy(): ?int
    {
        return $this->end_time_accuracy;
    }

    public function setEndTimeAccuracy(?int $end_time_accuracy): self
    {
        $this->end_time_accuracy = $end_time_accuracy;

        return $this;
    }

    public function getArchivePath(){
        if(is_null($this->parent)){
            return $this->abbreviation."/";
        }
        else {
            return $this->getParent()->getArchivePath().$this->abbreviation."/";
        }
    }

    /**
     * @return Collection|Series[]
     */
    public function getSeries(): Collection
    {
        return $this->series;
    }

    public function addSeries(Series $series): self
    {
        if (!$this->series->contains($series)) {
            $this->series[] = $series;
            $series->setArchive($this);
        }

        return $this;
    }

    public function removeSeries(Series $series): self
    {
        if ($this->series->contains($series)) {
            $this->series->removeElement($series);
            // set the owning side to null (unless already changed)
            if ($series->getArchive() === $this) {
                $series->setArchive(null);
            }
        }

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
            $institution->setArchive($this);
        }

        return $this;
    }

    public function removeInstitution(Institution $institution): self
    {
        if ($this->institutions->contains($institution)) {
            $this->institutions->removeElement($institution);
            // set the owning side to null (unless already changed)
            if ($institution->getArchive() === $this) {
                $institution->setArchive(null);
            }
        }

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
