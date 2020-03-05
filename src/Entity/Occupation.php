<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OccupationRepository")
 * @ORM\Table(name="occupations")
 */
class Occupation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $alt_names;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ActorOccupation", mappedBy="occupation", orphanRemoval=true)
     */
    private $actorsWithOccupation;

    public function __construct()
    {
        $this->actorsWithOccupation = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAltNames(): ?string
    {
        return $this->alt_names;
    }

    public function setAltNames(?string $alt_names): self
    {
        $this->alt_names = $alt_names;

        return $this;
    }

    /**
     * @return Collection|ActorOccupation[]
     */
    public function getActorsWithOccupation(): Collection
    {
        return $this->actorsWithOccupation;
    }

    public function addActorsWithOccupation(ActorOccupation $actorsWithOccupation): self
    {
        if (!$this->actorsWithOccupation->contains($actorsWithOccupation)) {
            $this->actorsWithOccupation[] = $actorsWithOccupation;
            $actorsWithOccupation->setOccupation($this);
        }

        return $this;
    }

    public function removeActorsWithOccupation(ActorOccupation $actorsWithOccupation): self
    {
        if ($this->actorsWithOccupation->contains($actorsWithOccupation)) {
            $this->actorsWithOccupation->removeElement($actorsWithOccupation);
            // set the owning side to null (unless already changed)
            if ($actorsWithOccupation->getOccupation() === $this) {
                $actorsWithOccupation->setOccupation(null);
            }
        }

        return $this;
    }
}
