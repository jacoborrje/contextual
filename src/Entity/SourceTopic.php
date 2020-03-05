<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use PhpParser\Node\Scalar\String_;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SourceTopicRepository")
 * @ORM\Table(name="sources_to_topics")
 */

class SourceTopic
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Source", inversedBy="sourceTopics")
     */
    private $source;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Topic", inversedBy="sources")
     */
    private $topic;

    /**
     * @ORM\Column(type="integer", length=11)
     */
    private $suggested;

    public function __clone() {
        if ($this->id) {
            $this->setId(null);
        }
    }

    public function __toString()
    {
        return $this->getTopic()->getTopic();
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

    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function setSource(?Source $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getTopic(): ?Topic
    {
        return $this->topic;
    }

    public function getTopicId(): ?int
    {
        return $this->getTopic()->getId();
    }

    public function setTopic(?Topic $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    public function getSuggested(){
        return $this->suggested;
    }
}