<?php

namespace App\Entity;

use App\Entity\Actor;
use App\Entity\PdfFile;
use App\Form\PdfFileType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PhpParser\Node\Scalar\String_;
use Doctrine\Common\Collections\Criteria;
use \DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SourceRepository")
 * @ORM\Table(name="sources")
 */

class Source
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Source", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Source", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $title;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date;

    private $text_date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $date_accuracy;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $excerpt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $transcription;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $research_notes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Volume", inversedBy="sources")
     */
    private $volume;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $place_in_volume;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $book;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    private $pages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DatabaseFile", mappedBy="source", orphanRemoval=true, cascade={"persist"})
     */
    private $files;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Action", mappedBy="source", orphanRemoval=true, cascade={"persist"})
     */
    private $actions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SourceTopic", mappedBy="source", cascade={"persist"})
     */
    private $sourceTopics;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mention", mappedBy="info_source", orphanRemoval=true, cascade={"persist"})
     */
    private $mentions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mention", mappedBy="mentioned_source", orphanRemoval=true, cascade={"persist"})
     */
    private $mentions_in_source;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $alvin_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $language;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->sourceTopics = new ArrayCollection();
        $this->mentions = new ArrayCollection();
        $this->mentions_in_source = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->title;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getListTitle(): ?string
    {
        if($this->getType() === 1){
            if($this->hasAuthors() && $this->hasRecipients())
                return "Letter from ".$this->getAuthorsAsString(true). " to ". $this->getRecipientsAsString(true);
            else if ($this->hasAuthors())
                return "Letter from ".$this->getAuthorsAsString(true);
            else if ($this->hasRecipients())
                return "Letter to ".$this->getRecipientsAsString(true);
            else
                return $this->getTitle();
        }
        else
            return $this->getTitle();
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getRawDate(){
        return $this->date;
    }

    public function getDate(): ?string
    {
        if(!is_null($this->date)) {
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->date, $this->date_accuracy);
            return $fuzzy_date->getDateString();
        }
        else
            return null;
    }

    /**
     * @return mixed
     */
    public function getTextDate()
    {
        if(!is_null($this->text_date)) {
            return $this->text_date;
        }
        else if(!is_null($this->date)&&!is_null($this->date_accuracy)){
            $fuzzy_date = new FuzzyDate();
            $fuzzy_date->setByDateAccuracy($this->date, $this->date_accuracy);
            return $fuzzy_date->getDateString();
        }
        else
            return "undated";
    }

    /**
     * @param mixed $text_date
     */
    public function setTextDate($text_date)
    {
        if(is_null($text_date)){
            $this->text_date =  "";
        }
        else {
            $this->text_date = $text_date;
        }
        return $this;
    }

    public function getSourcePath(){
        return $this->getVolume()->getVolumePath().$this->place_in_volume."/";
    }

    public function getVolumePath(){
        return $this->getVolume()->getVolumePath();
    }

    public function getArchive(){
        $root_series = $this->getVolume()->getSeries();
        while(is_null($root_series->getArchive())){
            $root_series = $root_series->getParent();
        }
        return $root_series->getArchive();
    }


    public function setDateByString(string $date): self
    {
        $fuzzy_date = new FuzzyDate();
        $fuzzy_date->setByDateString($date);
        $this->setDate($fuzzy_date->getFullDate());
        $this->setDateAccuracy($fuzzy_date->getAccuracy());
        return $this;
    }

    public function setDate(DateTime $date = null){
        $this->date = $date;
        return $this;
    }

    public function getDateAccuracy(): ?int
    {
        return $this->date_accuracy;
    }

    public function setDateAccuracy(?int $date_accuracy = null): self
    {
        $this->date_accuracy = $date_accuracy;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): self
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getTranscription(): ?string
    {
        return $this->transcription;
    }

    public function setTranscription(?string $transcription): self
    {
        $this->transcription = $transcription;

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

    public function getVolume(): ?Volume
    {
        return $this->volume;
    }

    public function setVolume(?Volume $volume): self
    {
        $this->volume = $volume;

        return $this;
    }

    public function getPlaceInVolume(): ?string
    {
        return $this->place_in_volume;
    }

    public function setPlaceInVolume(?string $place_in_volume): self
    {
        $this->place_in_volume = $place_in_volume;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getBook(): ?int
    {
        return $this->book;
    }

    public function setBook(?int $book): self
    {
        $this->book = $book;

        return $this;
    }

    public function getPages(): ?string
    {
        return $this->pages;
    }

    public function setPages(?string $pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    public function __clone(){
        if($this->id){
            $this->setId(null);
            $actionClone = new ArrayCollection();
            foreach($this->actions as $item){
                $itemClone = clone $item;
                $itemClone->setSource($this);
                $actionClone->add($itemClone);
            }
            $this->actions = $actionClone;
        }
    }

    /**
     * @return Collection|File[]
     */
    public function getFiles(): ?Collection
    {
        return $this->files;
    }

    public function setFiles(array $files = null): self
    {
        $this->files = $files;
        return $this;
    }

    public function getFile(): ?DatabaseFile
    {
        foreach($this->files as $file){
            return $file;
        }
        return null;
    }

    public function setFile(DatabaseFile $file = null):self
    {
        if(!is_null($file)) {
            $file->setSource($this);
            $this->files[0] = $file;
        }
        return $this;
    }

    public function addFile(DatabaseFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setSource($this);
        }

        return $this;
    }

    public function removeFile(DatabaseFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
            // set the owning side to null (unless already changed)
            if ($file->getSource() === $this) {
                $file->setSource(null);
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
            $action->setSource($this);
        }

        return $this;
    }

    public function getAuthors(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('type', 1));
        return $this->getActions()->matching($criteria);
    }

    public function hasAuthors(): bool
    {
        return count($this->getAuthors()) > 0;
    }

    public function getAuthorsAsString($etal = false, $makeFilename = false): string
    {
        $authors = $this->getAuthors();
        if(count($authors)===0)
            $authorString = "Anonymous";
        else if(count($authors)===1)
            $authorString = (string) $authors[0]->getCorrespondent();
        else{
            $authorString = "";
            if($etal) {
                $i = 0;
                foreach($authors as $author){
                    if($i < count($authors) && $i < $etal){
                        $authorString = $authorString . (string) $author->getCorrespondent() . ", ";
                        $i++;
                    }
                    else if($i < count($authors) && $i == $etal){
                        $authorString = substr($authorString,0,-2);
                        $authorString .= " et al";
                        $i = count($authors);
                    }
                }
            }
            else{
                $i = 0;
                foreach($authors as $author){
                    if($i < count($authors)){
                        $authorString = $authorString . (string) $author->getCorrespondent() . ", ";
                        $i++;
                    }
                }
                $authorString = substr($authorString,0,-2);
            }
        }
        if($makeFilename) {
            return str_replace(" ", "_", $authorString);
        }
        else
            return $authorString;
    }

    public function hasRecipients(): bool
    {
        return count($this->getRecipients())>0;
    }
    public function getRecipients(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('type', 2));
        return $this->getActions()->matching($criteria);
    }

    public function getRecipientsAsString($etal = false): string
    {
        $recipients = $this->getRecipients();
        if(count($recipients)===0)
            $recipientString = "Anonymous";
        else if(count($recipients)===1)
            $recipientString = (string) $recipients[0]->getCorrespondent();
        else{
            $recipientString = "";
            if($etal) {
                for ($i = 0; $i < count($authors) && $i < $etal; $i++){
                    $recipient = $recipients[$i];
                    $recipientString = $recipientString . (string) $recipient->getCorrespondent() . ", ";
                }
                if($i < count($authors) && $i > $etal){
                    $recipientString = substr($recipientString,0,-2);
                    $recipientString .= " et al";
                    $i = count($recipients);
                }
            }
            else{
                for ($i = 0; $i > count($recipients); $i++){
                    $recipient = $recipients[$i];
                    $recipientString = $recipientString . (string) $recipient->getCorrespondent() . ", ";
                }
                $recipientString = substr($recipientString,0,-2);
            }
        }
        return $recipientString;
    }


    public function removeAction(Action $action): self
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
            // set the owning side to null (unless already changed)
            if ($action->getSource() === $this) {
                $action->setSource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SourceTopic[]
     */
    public function getSourceTopics(): Collection
    {
        return $this->sourceTopics;
    }

    public function addSourceTopic(SourceTopic $sourceTopic): self
    {
        if (!$this->sourceTopics->contains($sourceTopic)) {
            $this->sourceTopics[] = $sourceTopic;
            $sourceTopic->setSource($this);
        }

        return $this;
    }

    public function removeSourceTopic(SourceTopic $sourceTopic): self
    {
        if ($this->sourceTopics->contains($sourceTopic)) {
            $this->sourceTopics->removeElement($sourceTopic);
            // set the owning side to null (unless already changed)
            if ($sourceTopic->getSource() === $this) {
                $sourceTopic->setSource(null);
            }
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
            $mention->setInfoSource($this);
        }

        return $this;
    }

    public function removeMention(Mention $mention): self
    {
        if ($this->mentions->contains($mention)) {
            $this->mentions->removeElement($mention);
            // set the owning side to null (unless already changed)
            if ($mention->getInfoSource() === $this) {
                $mention->setInfoSource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Mention[]
     */
    public function getMentionsInSource(): Collection
    {
        return $this->mentions_in_source;
    }

    public function addMentionsInSource(Mention $mention_in_source): self
    {
        if (!$this->mentions_in_source->contains($mention_in_source)) {
            $this->mentions_in_source[] = $mention_in_source;
            $mention_in_source->setMentionedSource($this);
        }

        return $this;
    }

    public function removeMentions_in_source(Mention $mention_in_source): self
    {
        if ($this->mentions_in_source->contains($mention_in_source)) {
            $this->mentions_in_source->removeElement($mention_in_source);
            // set the owning side to null (unless already changed)
            if ($mention_in_source->getMentionedSource() === $this) {
                $mention_in_source->setMentionedSource(null);
            }
        }

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

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeString(): ?string
    {
        switch ($this->type) {
            case 1: return "letter";
            case 2: return "book";
            case 3: return "document";
            case 4: return "article";
            case 5: return "travelogue";
            case 6: return "receipt";
            case 7: return "birth record";
            case 8: return "will";
            case 9: return "obituary";
            case 10: return "protocol";
        }
        return false;
    }

    public function getLanguageString(): ?string {
        switch ($this->language){
            case 1: return "Swedish";
            case 2: return "English";
            case 3: return "Latin";
            case 4: return "French";
            case 5: return "German";
            case 6: return "Dutch";
        }
        return false;
    }

    public function getShortLanguageString(): ?string {
        switch ($this->language){
            case 1: return "Swe";
            case 2: return "Eng";
            case 3: return "Lat";
            case 4: return "Fre";
            case 5: return "Ger";
            case 6: return "Dut";
        }
        return "-";
    }

    public function getSourceString(): ?string
    {
        if($this->type === 1 ) {
            $sourceString = $this->getTypeString();
            $sourceString .= ' from ' . $this->getAuthorsAsString(true);
            $sourceString .= ' to ' . $this->getRecipientsAsString(true);
            return $sourceString;
        }
        else {
            $sourceString = $this->getTypeString();
            $sourceString .= " by ";
            $sourceString .= $this->getAuthorsAsString(true);

            return $sourceString;
        }
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

    public function hasTopic($topic){
        foreach($this->getSourceTopics() as $sourceTopic){
            if($sourceTopic->getTopicId() === $topic->getId()) {
                return true;
            }
        }
        return false;
    }

    public function hasTopics(){
        if(count($this->getSourceTopics())>0)
            return 1;
        else
            return 0;
    }

    public function hasActions(){
        if(count($this->getActions())>0)
            return 1;
        else
            return 0;

    }

    public function hasTranscription(){
        if($this->getTranscription() !== "" && !is_null($this->getTranscription())){
            return 1;
        }
        else{
            return 0;
        }
    }

    public function getLanguage(): ?int
    {
        return $this->language;
    }

    public function setLanguage(?int $language): self
    {
        $this->language = $language;

        return $this;
    }

}