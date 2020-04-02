<?php
namespace App\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use App\Entity\Source;
use App\Entity\Place;
use App\Entity\Actor;
use Psr\Log\LoggerInterface;
use App\Utils\ActorService;


class EntitiesParser
{
    private $entityManager;
    private $text;
    private $source;
    private $logger;
    private $addedMarkers;
    private $actorService;

    function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, ActorService $actorService)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->actorService = $actorService;
    }

    public function initialize(Source $source)
    {
        $this->text = html_entity_decode($source->getTranscription());
        $this->source = $source;
        $this->addedMarkers = [];
    }

    public function parsePlaces(){
        $repository = $this->entityManager->getRepository(Place::class);
        $allPlaces = $repository->findAll();
        $foundPlaces = [];

        foreach($allPlaces as $place){
            $foundTokens = $this->findToken($place, $this->text, $place->getName());
            if($foundTokens) {
                $foundPlaces = array_merge($foundPlaces, $foundTokens);
            }
            if($place->hasAltNames()){
                $altNames = $place->getAltNamesArray();
                foreach($altNames as $altName){
                    $foundTokens = $this->findToken($place, $this->text, $altName);
                    if($foundTokens) {
                        $foundPlaces = array_merge($foundPlaces, $foundTokens);
                    }
                }
            }
        }
        return $foundPlaces;
    }

    public function findToken($object, $text, $searchString, $offset = 0){
        $matchString = "([\s\.,\r\n<>]".preg_quote($searchString)."[\s\.,\r\n<>s’'';])";
        preg_match ($matchString, $text,$matches, PREG_OFFSET_CAPTURE, $offset);
        if(count($matches)) {
            $foundToken = [$object, $matches[0][1],$matches[0][1]+strlen($searchString)+2];
            $this->logger->debug("The entities parser found a token: ". $object. " at position [".$foundToken[1]." to ".$foundToken[2]."] with name ".$searchString);
            $nextTokens = $this->findToken($object, $text, $searchString, $foundToken[2]);
            if($nextTokens) {
                $nextTokens[] = $foundToken;
                return $nextTokens;
            }
            else
                return [$foundToken];
        }
        else{
            return false;
        }
    }

    public function markTokens($tokens){
        $taggedText = $this->text;
        foreach ($tokens as $token){
            $previousTags = 0;
            foreach($tokens as $otherToken){
                if($otherToken['startPos']<$token['startPos'])
                    $previousTags++;
            }

            $startPos =  $this->getNewOffset($token['startPos']) + 1;
            $endPos = $this->getNewOffset($token['endPos']) + 2;
            $taggedText = substr_replace($taggedText, '<b>', $startPos, 0);
            $taggedText = substr_replace($taggedText, '</b>', $endPos, 0);
            $this->addedMarkers[] = [7,$token['endPos']];
            $this->logger->debug("Marking token ".$token['entity'].". Shifting position from ". $token['startPos']." to ".$startPos.".");
        }
        return $taggedText;
    }

    public function getNewOffset($oldPos){
        $offset = 0;
        foreach($this->addedMarkers as $addedMarker){
            if($addedMarker[1]<$oldPos){
                $offset += $addedMarker[0];
            }
        }
        return $oldPos + $offset;
    }

    public function parseActors(){
        $repository = $this->entityManager->getRepository(Actor::class);
        $allActors = $repository->findAll();
        $foundActors = [];

        foreach($allActors as $actor){
            $search_name = $actor->getFirstName()." ". $actor->getSurname();

            $foundTokens = $this->findToken($actor, $this->text, $search_name);
            if($foundTokens) {
                $foundActors = array_merge($foundActors, $foundTokens);
            }

            if($actor->hasAltFirstNames()){
                $altFirstNames = $actor->getAltFirstNamesArray();
                foreach($altFirstNames as $altFirstName){
                    $search_name = $altFirstName." ". $actor->getSurname();
                    $foundTokens = $this->findToken($actor, $this->text, $search_name);
                    if($foundTokens) {
                        $foundActors = array_merge($foundActors, $foundTokens);
                    }
                    if ($actor->hasAltSurnames()){
                        $altSurnames = $actor->getAltSurnamesArray();
                        foreach($altSurnames as $altSurname){
                            $search_name = $altFirstName." ". $altSurname;
                            $foundTokens = $this->findToken($actor, $this->text, $search_name);
                            if($foundTokens) {
                                $foundActors = array_merge($foundActors, $foundTokens);
                            }
                        }
                    }
                }
            }
            if ($actor->hasAltSurnames()){
                $altSurnames = $actor->getAltSurnamesArray();
                foreach($altSurnames as $altSurname){
                    $search_name = $actor->getFirstName()." ". $altSurname;
                    $foundTokens = $this->findToken($actor, $this->text, $search_name);
                    if($foundTokens) {
                        $foundActors = array_merge($foundActors, $foundTokens);
                    }
                    if ($actor->hasAltFirstNames()){
                        $altFirstNames = $actor->getAltFirstNamesArray();
                        foreach($altFirstNames as $altFirstName){
                            $search_name = $altFirstName." ". $altSurname;
                            $foundTokens = $this->findToken($actor, $this->text, $search_name);
                            if($foundTokens) {
                                $foundActors = array_merge($foundActors, $foundTokens);
                            }
                        }
                    }
                }
            }
            $initial = substr($actor->getFirstName(),0,1);
            $search_name = $initial.". ". $actor->getSurname();

            $foundTokens = $this->findToken($actor, $this->text, $search_name);
            if($foundTokens) {
                $foundActors = array_merge($foundActors, $foundTokens);
            }
            $search_name = $initial.": ". $actor->getSurname();
            $foundTokens = $this->findToken($actor, $this->text, $search_name);
            if($foundTokens) {
                $foundActors = array_merge($foundActors, $foundTokens);
            }
            $search_name = $initial." ". $actor->getSurname();
            $foundTokens = $this->findToken($actor, $this->text, $search_name);
            if($foundTokens) {
                $foundActors = array_merge($foundActors, $foundTokens);
            }
            if($this->actorService->hasUniqueSurname($actor)){
                $titles = ["Mr. ", "M[iste]r ", "M:r ",
                           "Herr. ", "Herr. ", "H. ", "H[err] ", "H:r ", "H.r ",
                           "D[octo]r ", "Dr ", "Dr. ", "Herr Doktor ", "Doktor ", "Doctor ",
                           "Archiater ", "Baron ", "H[err] Baron ",
                            "Envoyen ", "Envoye ",
                           "Greve ", "H[err] Vice Praesid[ent] ", "gref ",
                           "President ", "Secreterare ", "H[err] President",
                           "H[err] Secreterare ", "H[err] Vice Praesid[ent] B[aron] ",
                           "H[err] Mag[ister] ", "Mag. ", "H:r Magist: ", "Magister ",
                           "Capt. ", "Captain ", "Kapten ", "Capten ",
                           "Biskopen "];

                foreach($titles as $title){
                    $search_name = $title.$actor->getSurname();
                    $foundTokens = $this->findToken($actor, $this->text, $search_name);
                    if($foundTokens) {
                        $foundActors = array_merge($foundActors, $foundTokens);
                    }

                    if ($actor->hasAltSurnames()) {
                        $altSurnames = $actor->getAltSurnamesArray();
                        foreach ($altSurnames as $altSurname) {
                            $search_name = $title . $altSurname;
                            $foundTokens = $this->findToken($actor, $this->text, $search_name);
                            if ($foundTokens) {
                                $foundActors = array_merge($foundActors, $foundTokens);
                            }
                        }
                    }
                }
            }
        }

        if(count($foundActors))
            return $foundActors;
        else
            return false;
    }
}

?>