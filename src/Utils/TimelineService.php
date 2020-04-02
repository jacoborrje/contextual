<?php
namespace App\Utils;

use App\Entity\Institution;
use App\Entity\Timeline;
use App\Entity\Actor;
use \App\Entity\TimelineEntry;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class TimelineService
{
    private $geographyHelper, $timeline;
    private $router;

    function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    function createActorTimeline(Actor $actor, $removeUndated = false, $onlyUndated = false, $onlyGeographical = false)
    {
        $this->timeline = new Timeline();
        $this->timeline->setActor($actor);

        if(!is_null($actor->getBirthdate())) {
            $birthEntry = new TimelineEntry($actor->getBirthdate(), $actor->getBirthdateAccuracy(), $actor, [$actor], "was born", $actor->getBirthplace());
            $birthEntry = $this->createEntryString($birthEntry);
            $birthEntry->setIconUrl('images/birthdate.png');
            $this->timeline->addEntry($birthEntry);
        }

        foreach($actor->getActions() as $action){
            if($action->getType()===1){
                $authorEntry = new TimelineEntry($action->getRawStartDate(), $action->getStartDateAccuracy(), $actor->getCorrespondent(), $actor->getCorrespondent(), 'wrote', $action->getSource(), $action->getSource()->getRecipients());
                $authorEntry = $this->createEntryString($authorEntry);
                $this->timeline->addEntry ($authorEntry);
            }
            if($action->getType()===2){
                $recipientEntry = new TimelineEntry($action->getRawStartDate(), $action->getStartDateAccuracy(), $actor->getCorrespondent(), $actor->getCorrespondent(), 'received', $action->getSource(), $action->getSource()->getAuthors());
                $recipientEntry = $this->createEntryString($recipientEntry);
                $this->timeline->addEntry ($recipientEntry);
            }
        }


        foreach ($actor->getPlaces() as $place){
            if(!is_null($place->getPlace())) {
                if ($place->getType() == 0) {
                    $placeEntry = new TimelineEntry($place->getDateOfArrival(), $place->getDateOfArrivalAccuracy(), $actor->getCorrespondent(), $actor->getCorrespondent(), 'was born', $place->getPlace(), null, false, false);
                    $placeEntry = $this->createEntryString($placeEntry);
                    $this->timeline->addEntry($placeEntry);
                }
                if ($place->getType() == 1) {
                    $placeEntry = new TimelineEntry($place->getDateOfArrival(), $place->getDateOfArrivalAccuracy(), $actor->getCorrespondent(), $actor->getCorrespondent(), 'died', $place->getPlace(), null, false, false);
                    $placeEntry = $this->createEntryString($placeEntry);
                    $this->timeline->addEntry($placeEntry);
                }
                if ($place->getType() == 2) {
                    $placeEntry = new TimelineEntry($place->getDateOfArrival(), $place->getDateOfArrivalAccuracy(), $actor->getCorrespondent(), $actor->getCorrespondent(), 'moved', $place->getPlace(), null, false, false);
                    $placeEntry = $this->createEntryString($placeEntry);
                    $this->timeline->addEntry($placeEntry);
                }
                if ($place->getType() == 3) {
                    $placeEntry = new TimelineEntry($place->getDateOfArrival(), $place->getDateOfArrivalAccuracy(), $actor->getCorrespondent(), $actor->getCorrespondent(), 'began working', $place->getPlace(), null, false, false);
                    $placeEntry = $this->createEntryString($placeEntry);
                    $this->timeline->addEntry($placeEntry);
                }
                if ($place->getType() == 4) {
                    $placeEntry = new TimelineEntry($place->getDateOfArrival(), $place->getDateOfArrivalAccuracy(), $actor->getCorrespondent(), $actor->getCorrespondent(), 'emigrated', $place->getPlace(), null, false, false);
                    $placeEntry = $this->createEntryString($placeEntry);
                    $this->timeline->addEntry($placeEntry);
                }
                if ($place->getType() == 5) {
                    $placeEntry = new TimelineEntry($place->getDateOfArrival(), $place->getDateOfArrivalAccuracy(), $actor->getCorrespondent(), $actor->getCorrespondent(), 'travelled', $place->getPlace(), null, false, false);
                    $placeEntry = $this->createEntryString($placeEntry);
                    $this->timeline->addEntry($placeEntry);
                }
            }
        }

        foreach($actor->getMentions() as $mention){
            if($mention->getVerb() === 'arrived' && !is_null($mention->getEventPlace())){
                $mentionEntry = new TimelineEntry($mention->getDate(), $mention->getDateAccuracy(),  $actor->getCorrespondent(),  $actor->getCorrespondent(), 'arrived', $mention->getEventPlace(), null, null, false, $mention->getInfoSource(), [$mention->getStartPage(), $mention->getEndPage()], $mention->getTime());
            }
            else if($mention->getVerb() === 'dined' && !is_null($mention->getEventPlace())){
                $mentionEntry = new TimelineEntry($mention->getDate(), $mention->getDateAccuracy(),  $actor->getCorrespondent(),  $actor->getCorrespondent(), 'dined', $mention->getInstitution(), $mention->getEventPlace(), null, false, $mention->getInfoSource(), [$mention->getStartPage(), $mention->getEndPage()], $mention->getTime());
            }
            else if($mention->getVerb() === 'passed' && !is_null($mention->getEventPlace())){
                $mentionEntry = new TimelineEntry($mention->getDate(), $mention->getDateAccuracy(),  $actor->getCorrespondent(),  $actor->getCorrespondent(), 'passed',  $mention->getEventPlace(), null,null, false, $mention->getInfoSource(), [$mention->getStartPage(), $mention->getEndPage()], $mention->getTime());
            }
            else if($mention->getVerb() === 'slept' && !is_null($mention->getEventPlace())){
                $mentionEntry = new TimelineEntry($mention->getDate(), $mention->getDateAccuracy(),  $actor->getCorrespondent(),  $actor->getCorrespondent(), 'slept', $mention->getInstitution(), $mention->getEventPlace(), null, false, $mention->getInfoSource(), [$mention->getStartPage(), $mention->getEndPage()], $mention->getTime());
            }
            else if($mention->getVerb() === 'rested' && !is_null($mention->getEventPlace())){
                $mentionEntry = new TimelineEntry($mention->getDate(), $mention->getDateAccuracy(),  $actor->getCorrespondent(),  $actor->getCorrespondent(), 'rested', $mention->getInstitution(), $mention->getEventPlace(), null, false, $mention->getInfoSource(), [$mention->getStartPage(), $mention->getEndPage()], $mention->getTime());
            }
            else if($mention->getVerb() === 'visited' && !is_null($mention->getEventPlace())){
                $mentionEntry = new TimelineEntry($mention->getDate(), $mention->getDateAccuracy(),  $actor->getCorrespondent(),  $actor->getCorrespondent(), 'visited', $mention->getInstitution(), $mention->getEventPlace(), null, false, $mention->getInfoSource(), [$mention->getStartPage(), $mention->getEndPage()], $mention->getTime());
            }
            else if($mention->getVerb() === 'acquired' && !is_null($mention->getEventPlace())){
                $mentionEntry = new TimelineEntry($mention->getDate(), $mention->getDateAccuracy(),  $actor->getCorrespondent(),  $actor->getCorrespondent(), 'acquired', $mention->getEventPlace(), null, null, false, $mention->getInfoSource(), [$mention->getStartPage(), $mention->getEndPage()], $mention->getTime());
            }
            else if($mention->getVerb() === 'departed' && !is_null($mention->getEventPlace())){
                $mentionEntry = new TimelineEntry($mention->getDate(), $mention->getDateAccuracy(),  $actor->getCorrespondent(),  $actor->getCorrespondent(), 'departed', $mention->getEventPlace(), null, null, false, $mention->getInfoSource(), [$mention->getStartPage(), $mention->getEndPage()], $mention->getTime());
            }
            else {
                $mentionEntry = new TimelineEntry($mention->getDate(), $mention->getDateAccuracy(), $actor->getCorrespondent(), $actor->getCorrespondent(), 'was mentioned', $mention->getInfoSource(), $mention->getInfoSource()->getAuthors(), null, false, null, null, $mention->getTime());
            }
            $mentionEntry = $this->createEntryString($mentionEntry);
            $this->timeline->addEntry ($mentionEntry );
        }

        foreach($actor->getOccupations() as $actorOccupation){
            if(!is_null($actorOccupation->getStartDate())) {
                $occupationEntry = new TimelineEntry($actorOccupation->getStartDate(), $actorOccupation->getStartDateAccuracy(), $actor->getCorrespondent(), $actor->getCorrespondent(), 'became', $actorOccupation->getOccupation(), $actorOccupation->getInstitution());
                $occupationEntry = $this->createEntryString($occupationEntry);
                $this->timeline->addEntry ($occupationEntry );

            }
            if(!is_null($actorOccupation->getEndDate())) {
                $occupationEntry = new TimelineEntry($actorOccupation->getEndDate(), $actorOccupation->getEndDateAccuracy(), $actor->getCorrespondent(), $actor->getCorrespondent(), 'stopped being', $actorOccupation->getOccupation(), $actorOccupation->getInstitution());
                $occupationEntry = $this->createEntryString($occupationEntry);
                $this->timeline->addEntry ($occupationEntry );
            }
        }

        foreach($actor->getRelationships() as $relationship){
            $relationshipType = $relationship->getTypeString();
            $relatedActor = $relationship->getRelatedActor()->getCorrespondent();
            if($relationshipType === "mother" || $relationshipType === 'father'){
                if($relatedActor->getActor()->getDateOfDeath()<$actor->getDateOfDeath()) {
                    $deathEntry = new TimelineEntry($relatedActor->getActor()->getDateOfDeath(), $relatedActor->getActor()->getDateOfDeathAccuracy(), $relatedActor, [$relatedActor], "died", $relatedActor->getActor()->getPlaceOfDeath(), null, ucfirst($actor->getPossessivePronoun()) . " " . $relationshipType, true);
                    $deathEntry = $this->createEntryString($deathEntry);
                    $this->timeline->addEntry($deathEntry);
                }
            }
            if($relationshipType === "brother" || $relationshipType === 'sister'){
                if($relatedActor->getActor()->getDateOfDeath()>$actor->getDateOfDeath()) {
                    $birthEntry = new TimelineEntry($relatedActor->getActor()->getBirthdate(), $relatedActor->getActor()->getBirthdateAccuracy(), $relatedActor, [$relatedActor], "was born", $relatedActor->getActor()->getBirthplace(), null, ucfirst($actor->getPossessivePronoun()) . " " . $relationshipType, true);
                    $birthEntry = $this->createEntryString($birthEntry);
                    $birthEntry->setIconUrl('images/birthdate.png');
                    $this->timeline->addEntry($birthEntry);
                }
                if($relatedActor->getActor()->getDateOfDeath()<$actor->getDateOfDeath()) {
                    $deathEntry = new TimelineEntry($relatedActor->getActor()->getDateOfDeath(), $relatedActor->getActor()->getDateOfDeathAccuracy(), $relatedActor, [$relatedActor], "died", $relatedActor->getActor()->getPlaceOfDeath(), null, ucfirst($actor->getPossessivePronoun()) . " " . $relationshipType, true);
                    $deathEntry = $this->createEntryString($deathEntry);
                    $this->timeline->addEntry($deathEntry);
                }
            }
            if($relationshipType === "son" || $relationshipType === 'daughter'){
                $birthEntry = new TimelineEntry($relatedActor->getActor()->getBirthdate(),  $relatedActor->getActor()->getBirthdateAccuracy(), $relatedActor, [$relatedActor], "was born", $relatedActor->getActor()->getBirthplace(), null, ucfirst($actor->getPossessivePronoun())." ".$relationshipType, true);
                $birthEntry = $this->createEntryString($birthEntry);
                $birthEntry->setIconUrl('images/birthdate.png');
                $this->timeline->addEntry ($birthEntry);

                if($relatedActor->getActor()->getDateOfDeath()<$actor->getDateOfDeath()) {
                    $deathEntry = new TimelineEntry($relatedActor->getActor()->getDateOfDeath(), $relatedActor->getActor()->getDateOfDeathAccuracy(), $relatedActor, [$relatedActor], "died", $relatedActor->getActor()->getPlaceOfDeath(), null, ucfirst($actor->getPossessivePronoun()) . " " . $relationshipType, true);
                    $deathEntry = $this->createEntryString($deathEntry);
                    $this->timeline->addEntry($deathEntry);
                }
            }

            if($relationshipType === "wife" || $relationshipType === 'husband'){
                $marriageEntry = new TimelineEntry($relationship->getStartDate(),  $relationship->getStartDateAccuracy(), $actor->getCorrespondent(), [$actor->getCorrespondent()], "married", $relatedActor, null, null, false);
                $marriageEntry = $this->createEntryString($marriageEntry);
                $marriageEntry->setIconUrl('images/marriage.png');
                $this->timeline->addEntry ($marriageEntry);

            }

        }

        if(!is_null($actor->getDateOfDeath())) {
            $deathEntry = new TimelineEntry($actor->getDateOfDeath(), $actor->getDateOfDeathAccuracy(), $actor, [$actor], "died", $actor->getPlaceOfDeath());
            $deathEntry = $this->createEntryString($deathEntry);
            $this->timeline->addEntry($deathEntry);
        }
        $this->timeline->sort();
        if($onlyGeographical){
            $this->timeline->removeUngeographical();
        }
        return $this->timeline;
    }

    function createInstitutionTimeline(Institution $institution){
        $this->timeline = new Timeline();
        $this->timeline->setInstitution($institution);
        if(!is_null($institution->getDateOfEstablishment())) {
            $establishedEntry = new TimelineEntry($institution->getDateOfEstablishment(), $institution->getDateOfEstablishmentAccuracy(), $institution->getCorrespondent(), [$institution->getCorrespondent()], "was established", $institution->getPlace(), $institution->getTextDateOfEstablishment());
            $establishedEntry = $this->createEntryString($establishedEntry);
            $this->timeline->addEntry($establishedEntry);
        }

        foreach($institution->getActions() as $action){
            if($action->getType()===1){
                $authorEntry = new TimelineEntry($action->getRawStartDate(), $action->getStartDateAccuracy(), $institution->getCorrespondent(), $institution->getCorrespondent(), 'wrote', $action->getSource(), $action->getSource()->getRecipients());
                $authorEntry = $this->createEntryString($authorEntry);
                $this->timeline->addEntry ($authorEntry);
            }
            if($action->getType()===2){
                $recipientEntry = new TimelineEntry($action->getRawStartDate(), $action->getStartDateAccuracy(), $institution->getCorrespondent(), $institution->getCorrespondent(), 'received', $action->getSource(), $action->getSource()->getAuthors());
                $recipientEntry = $this->createEntryString($recipientEntry);
                $this->timeline->addEntry ($recipientEntry);
            }
        }

        foreach($institution->getMentions() as $mention){
            $mentionEntry = new TimelineEntry($mention->getDate(), $mention->getDateAccuracy(), $institution->getCorrespondent(), $institution->getCorrespondent(), 'was mentioned', $mention->getInfoSource(), $mention->getInfoSource()->getAuthors());
            $mentionEntry = $this->createEntryString($mentionEntry);
            $this->timeline->addEntry ($mentionEntry );
        }

        foreach($institution->getMembers() as $member){
            if(!is_null($member->getStartDate())){
                $mentionEntry = new TimelineEntry($member->getStartDate(), $member->getStartDateAccuracy(), $member->getActor()->getCorrespondent(), $member->getActor()->getCorrespondent(), 'became', $member->getOccupation(), $member->getInstitution());
                $mentionEntry = $this->createEntryString($mentionEntry, true);
                $this->timeline->addEntry ($mentionEntry);
            }
        }


        if(!is_null($institution->getDateOfDissolution())) {
            $establishedEntry = new TimelineEntry($institution->getDateOfDissolution(), $institution->getDateOfDissolutionAccuracy(), $institution, [$institution], "was dissolved", $institution->getPlace(), $institution->getTextDateOfDissolution());
            $establishedEntry = $this->createEntryString($establishedEntry);
            $this->timeline->addEntry($establishedEntry);

        }

        $this->timeline->sort();
        return $this->timeline;
    }

    function createEntryString(TimelineEntry $entry, $addSubjectLink = false){
        $entryString = "";
        if(!is_null($entry->getInitialText())){
            $entryString .= $entry->getInitialText(). " ";
        }
        if($addSubjectLink || $entry->displaySubjectLink()){
            if(!is_null($entry->getPrimarySubject()->getActor())){
                $actorId = $entry->getPrimarySubject()->getActor()->getId();
                $correspondentUrl = $this->router->generate('actor_view', array('actor_id' => $actorId));
            }
            else {
                $institutionId = $entry->getPrimarySubject()->getInstitution()->getId();
                $correspondentUrl = $this->router->generate('institution_view', array('institution_id' => $institutionId));
            }
            $entryString .=  '<a href="'.$correspondentUrl.'">' . $entry->getPrimarySubject() . "</a>";
        }
        else
            $entryString .= (string) $entry->getPrimarySubject();
        $entryString .= " ".$entry->getVerb();
        if(($entry->getDirectObjectType()!=='null') && !is_null($entry->getDirectObject())) {
            if(!is_null($entry->getDirectObjectPrep())){
                $entryString .= " " . $entry->getDirectObjectPrep();
            }
            else {
                $entryString .= " ";
            }
            if($entry->getDirectObjectClass()==='Source') {
                $sourceUrl = $this->router->generate('source_edit', array('source_id' => $entry->getDirectObject()->getId()));
                if($entry->getDirectObject()->getTypeString() === 'document' || $entry->getDirectObject()->getTypeString() === 'book'){
                    $entryString = substr($entryString, 0,strlen($entryString)-1);
                    $entryString .= 'the '.$entry->getDirectObject()->getTypeString().' <a href="' . $sourceUrl . '"> ' . $entry->getDirectObject()->getTitle() . '</a>';
                }
                else{
                    $entryString .= ' <a href="' . $sourceUrl . '"> ' . $entry->getDirectObject()->getTypeString() . '</a>';
                }
            }
            else if ($entry->getDirectObjectClass()==='Correspondent') {
                if(!is_null($entry->getDirectObject()->getActor())){
                    $actorId = $entry->getDirectObject()->getActor()->getId();
                    $correspondentUrl = $this->router->generate('actor_view', array('actor_id' => $actorId));
                }
                else {
                    $institutionId = $entry->getDirectObject()->getInstitution()->getId();
                    $correspondentUrl = $this->router->generate('institution_view', array('institution_id' => $institutionId));
                }
                $entryString = $entryString .'<a href="'.$correspondentUrl.'">' . $entry->getDirectObject() . "</a>";
            }
            else if($entry->getDirectObjectClass()==='Occupation') {
                $occupationUrl = $this->router->generate('occupation_view', array('occupation_id' => $entry->getDirectObject()->getId()));
                $entryString .= ' <a href="' . $occupationUrl . '"> ' . lcfirst($entry->getDirectObject()) . '</a>';
            }
            else if ($entry->getDirectObjectClass()==='Place') {
                $placeUrl = $this->router->generate('place_view', array('place_id' => $entry->getDirectObject()->getId()));
                $entryString .= ' <a href="'.$placeUrl.'">'. $entry->getDirectObject().'</a>';
            }
            else{
                $entryString .= " " . $entry->getDirectObject();
            }
        }
        if($entry->getIndirectObjectType()!=='null' && $entry->getIndirectObject() !== null ) {
            $break = false;
            if(is_countable($entry->getIndirectObject())){
                if(count($entry->getIndirectObject()) < 1) {
                    $break = true;
                }
            }
            if(!is_null($entry->getIndirectObjectPrep()) && !$break){
                $entryString .= " ".$entry->getIndirectObjectPrep();
            }
            if ($entry->getIndirectObjectType()==='Action[]' && !$break) {
                $recipientString = "";
                $i = 1;
                foreach ($entry->getIndirectObject() as $action){
                    $institution = false;
                    if($i<count($entry->getIndirectObject())&&$i>1){
                        $recipientString .= ", ";
                    }
                    else if ($i == count($entry->getIndirectObject())&&$i>1){
                        $recipientString .= " and ";
                        }
                    if(!is_null($action->getCorrespondent()->getActor())){
                        $actorId = $action->getCorrespondent()->getActor()->getId();
                        $correspondentUrl = $this->router->generate('actor_view', array('actor_id' => $actorId));
                    }
                    else{
                        $institutionId = $action->getCorrespondent()->getInstitution()->getId();
                        $correspondentUrl = $this->router->generate('institution_view', array('institution_id' => $institutionId));
                        $institution = true;
                    }
                    if(!$institution) $recipientString = $recipientString .'<a href="'.$correspondentUrl.'">' . $action->getCorrespondent() . "</a>";
                    else $recipientString = $recipientString .'the <a href="'.$correspondentUrl.'">' . $action->getCorrespondent() . "</a>";
                    $i++;
                }
                $entryString .= " " . $recipientString;
            }
            else if ($entry->getIndirectObjectType()==='Institution' && !$break ) {
                $institutionUrl = $this->router->generate('institution_view', array('institution_id' => $entry->getIndirectObject()->getId()));
                $entryString .= ' <a href="' . $institutionUrl . '"> ' . $entry->getIndirectObject() . '</a>';

            }
            else if ($entry->getIndirectObjectType()==='Place' && !$break ) {
                $placeUrl = $this->router->generate('place_view', array('place_id' => $entry->getIndirectObject()->getId()));
                $entryString .= ' <a href="' . $placeUrl . '"> ' . $entry->getIndirectObject() . '</a>';

            }
            else if (!is_null($entry->getIndirectObject()) && !$break){
                $entryString .= " " . $entry->getIndirectObject();
            }
        }
        if(count($entry->getCompany())>0){
            $i = 0;
            foreach($entry->getCompany() as $fellowSubject){
                if($i === 1){
                    $entryString .= " with ";
                    $entryString .= (string) $fellowSubject;
                }
                else if($i == count($entry->getCompany()) && count($entry->getCompany())>1){
                    $entryString .= " and ". (string) $fellowSubject;
                }
                else{
                    $entryString .= ", ". (string) $fellowSubject;
                }
            }
        }
        if(!is_null($entry->getSource())){
            $sourceUrl = $this->router->generate('source_edit', array('source_id' => $entry->getSource()->getId()));
            $sourceString = "source: ";
            if($entry->getSource()->getTypeString() === 'document' || $entry->getSource()->getTypeString() === 'book'){
                $sourceString = substr($sourceString, 0,strlen($entryString)-1);
                $sourceString .= '<a href="' . $sourceUrl . '"> ' . $entry->getSource()->getTitle() . '</a>';
            }
            else{
                $sourceString .= ' <a href="' . $sourceUrl . '"> ' . $entry->getSource()->getTypeString() . '</a>';
            }
            $pages =$entry->getSourcePages();
            $pagesString = "p. ";

            if(!is_null($pages[0])){
                $pagesString .= $pages [0];
            }
            if(!is_null($pages[1])){
                $pagesString .= "–". $pages[1];
            }
            $entryString .= " (".$sourceString.", ".$pagesString.")";
        }
        $entryString .= ".";


        $entry->setEntryString($entryString);
        return $entry;
    }
}
?>