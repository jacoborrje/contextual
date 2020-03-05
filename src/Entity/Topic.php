<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TopicRepository")
 * @ORM\Table(name="topics")
 */
class Topic
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ORM\JoinColumn(name="productId", referencedColumnName="id")
     */
    private $topic;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SourceTopic", mappedBy="topic")
     *
     */
    private $sources;

    public function __construct()
    {
        $this->sources = new ArrayCollection();
    }

    public function  __toString()
    {
        return $this->topic;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(?string $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * @return Collection|SourceTopic[]
     */
    public function getSources(): Collection
    {
        return $this->sources;
    }

    public function getNumberOfSources(): int {
        return count($this->sources);
    }

    public function addSource(SourceTopic $source): self
    {
        if (!$this->sources->contains($source)) {
            $this->sources[] = $source;
            $source->setTopic($this);
        }

        return $this;
    }

    public function removeSource(SourceTopic $source): self
    {
        if ($this->sources->contains($source)) {
            $this->sources->removeElement($source);
            // set the owning side to null (unless already changed)
            if ($source->getTopic() === $this) {
                $source->setTopic(null);
            }
        }

        return $this;
    }
}
