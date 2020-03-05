<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VolumeRepository")
 * @ORM\Table(name="volumes")
 */
class Volume
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
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $abbreviation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Series", inversedBy="volumes")
     */
    private $series;

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

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $end_date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $end_date_accuracy;

    private $text_start_date;
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
     * @ORM\OneToMany(targetEntity="App\Entity\Source", mappedBy="volume")
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $sources;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    public function __construct()
    {
        $this->sources = new ArrayCollection();
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

    public function setAbbreviation(?string $abbreviation): self
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function getSeries(): ?Series
    {
        return $this->series;
    }

    public function setSeries(?Series $series): self
    {
        $this->series = $series;

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

    public function getFuzzyStartDate(): ?FuzzyDate
    {
        $fuzzyStartDate = new FuzzyDate();
        $fuzzyStartDate->setByDateAccuracy($this->start_date,$this->start_date_accuracy);
        return $fuzzyStartDate;
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

    public function getFuzzyEndDate(): ?FuzzyDate
    {
        $fuzzyEndDate = new FuzzyDate();
        $fuzzyEndDate->setByDateAccuracy($this->end_date,$this->end_date_accuracy);
        return $fuzzyEndDate;
    }

    public function getVolumePath(){
        return $this->getSeries()->getSeriesPath().$this->getAbbreviation()."/";

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

    /**
     * @return Collection|Source[]
     */
    public function getSources(): Collection
    {
        return $this->sources;
    }

    public function addSource(Source $source): self
    {
        if (!$this->sources->contains($source)) {
            $this->sources[] = $source;
            $source->setVolume($this);
        }

        return $this;
    }

    public function removeSource(Source $source): self
    {
        if ($this->sources->contains($source)) {
            $this->sources->removeElement($source);
            // set the owning side to null (unless already changed)
            if ($source->getVolume() === $this) {
                $source->setVolume(null);
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

    public function getNumberOfSources(): ?int
    {
        return count($this->getSources());
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
