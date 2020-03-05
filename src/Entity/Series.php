<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SeriesRepository")
 * @ORM\Table(name="series")
 */
class Series
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
     * @ORM\Column(type="string", length=64)
     */
    private $abbreviation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Archive", inversedBy="series")
     */
    private $archive;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $external_link;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $start_date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $start_date_accuracy;

    private $text_start_date;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $end_date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $end_date_accuracy;

    private $text_end_date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $research_notes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Series", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Series", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Volume", mappedBy="series")
     */
    private $volumes;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->volumes = new ArrayCollection();
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

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(string $abbreviation): self
    {
        $this->abbreviation = $abbreviation;

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

    public function getExternalLink(): ?string
    {
        return $this->external_link;
    }

    public function setExternalLink(?string $external_link): self
    {
        $this->external_link = $external_link;

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
        return $this->end_date;
    }

    public function setEndDate(?\DateTimeInterface $end_date): self
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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getSeriesPath()
    {
        if (is_null($this->getParent())) {
            if (is_null($this->archive)) {
                return $this->getAbbreviation() . "/";
            } else
                return $this->getArchive()->getArchivePath() . $this->getAbbreviation() . "/";
        } else {
            return $this->parent->getSeriesPath() . $this->abbreviation . "/";
        }
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

    /**
     * @return Collection|Volume[]
     */
    public function getVolumes(): Collection
    {
        return $this->volumes;
    }

    public function addVolume(Volume $volume): self
    {
        if (!$this->volumes->contains($volume)) {
            $this->volumes[] = $volume;
            $volume->setSeries($this);
        }

        return $this;
    }

    public function removeVolume(Volume $volume): self
    {
        if ($this->volumes->contains($volume)) {
            $this->volumes->removeElement($volume);
            // set the owning side to null (unless already changed)
            if ($volume->getSeries() === $this) {
                $volume->setSeries(null);
            }
        }

        return $this;
    }


    /**
     * @return mixed
     */
    public function getTextStartDate()
    {
        if (!is_null($this->start_date) && !is_null($this->start_date_accuracy)) {
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->start_date, $this->start_date_accuracy);
            return $fuzzy_date->getDateString();
        } else if (!is_null($this->text_start_date))
            return $this->text_start_date;
        else
            return "????";
    }

    /**
     * @param mixed $text_start_date
     */
    public function setTextStartDate($text_start_date)
    {
        $this->text_start_date = $text_start_date;
    }

    /**
     * @return mixed
     */
    public function getTextEndDate()
    {
        if (!is_null($this->end_date) && !is_null($this->end_date_accuracy)) {
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->end_date, $this->end_date_accuracy);
            return $fuzzy_date->getDateString();
        } else if (!is_null($this->text_end_date))
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