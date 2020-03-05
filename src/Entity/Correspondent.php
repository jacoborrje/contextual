<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CorrespondentRepository")
 * @ORM\Table(name="correspondents")
 */
class Correspondent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Actor", inversedBy="correspondent", cascade={"persist"})
     */
    private $actor;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Institution", inversedBy="correspondent", cascade={"persist"})
     */
    private $institution;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Action", mappedBy="correspondent")
     */
    private $actions;

    public function __toString()
    {
        if(!is_null($this->actor))
            return $this->getActor()->getFirstName() . ' ' . $this->getActor()->getSurname();
        else
            return $this->getInstitution()->getName();
    }

    public function __construct()
    {
        $this->actions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActor(): ?Actor
    {
        return $this->actor;
    }

    public function setActor(?Actor $actor = null): self
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
            $action->setCorrespondent($this);
        }

        return $this;
    }

    public function removeAction(Action $action): self
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
            // set the owning side to null (unless already changed)
            if ($action->getCorrespondent() === $this) {
                $action->setCorrespondent(null);
            }
        }

        return $this;
    }
}
