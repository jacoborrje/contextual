<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;


/**
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 * @ORM\Table(name="files")
 */
class DatabaseFile
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    private $mimeType;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $name;

    private $originalName;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Source", inversedBy="files")
     */
    private $source;


    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $path;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="image", cascade={"persist", "remove"})
     */
    private $place;

    private $fileContents;

    /**
     * @return mixed
     */
    public function getFileContents()
    {
        return $this->fileContents;
    }

    /**
     * @param mixed $fileContents
     */
    public function setFileContents($fileContents)
    {
        $this->fileContents = $fileContents;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name = null): void
    {
        $this->name = $name;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size = null): void
    {
        $this->size = $size;
    }

    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function setSource(?Source $source): ?self
    {
        $this->source = $source;

        return $this;
    }

    public function setPath(?string $path): ?self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @param mixed $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return mixed
     */
    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    /**
     * @param mixed $originalName
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

        // set (or unset) the owning side of the relation if necessary
        $newImage = $place === null ? null : $this;
        if ($newImage !== $place->getImage()) {
            $place->setImage($newImage);
        }

        return $this;
    }

    public function getThumbnailName(){
        $base_name = substr($this->name, 0, -4);
        $extension = substr($this->name, -4);
        return $base_name."_thumb".$extension;
    }

    public function getPathname(){
        return $this->path . $this->name;
    }
}