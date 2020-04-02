<?php
namespace App\Utils;

use \App\Entity\Actor;
use App\Entity\Correspondent;
use Doctrine\ORM\EntityManagerInterface;


class ActorService
{

    protected $entityManager;
    protected $actorRepository;


    function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->actorRepository = $this->entityManager->getRepository(Actor::class);

    }

    function merge(Actor $existingActor, Actor $newActor)
    {
        if(is_null($existingActor->getGender()) && !is_null($newActor->getGender())){
            $existingActor->setGender($newActor->getGender());
        }

        if(is_null($existingActor->getDescription()) && !is_null($newActor->getDescription())){
            $existingActor->setDescription($newActor->getDescription());
        }

        if(is_null($existingActor->getResearchNotes()) && !is_null($newActor->getResearchNotes())){
            $existingActor->setResearchNotes($newActor->getResearchNotes());
        }

        if(is_null($existingActor->getAltFirstNames()) && !is_null($newActor->getAltFirstNames())){
            $existingActor->setAltFirstNames($newActor->getAltFirstNames());
        }
        else if(!is_null($existingActor->getAltFirstNames()) && !is_null($newActor->getAltFirstNames())){
            $oldAltFirstNames = explode(", ", $existingActor->getAltFirstNames());
            $newAltFirstNames = explode(", ",$newActor->getAltFirstNames());

            foreach($newAltFirstNames as $firstName){
                if(!in_array ( $firstName, $oldAltFirstNames)){
                    $oldAltFirstNames[] = $firstName;
                }
            }
            $altFirstNamesString = "";
            foreach($oldAltFirstNames as $firstName){
                $altFirstNamesString .= $firstName . ", ";
            }
            $altFirstNamesString = substr($altFirstNamesString, 0, -2);
            $existingActor->setAltFirstNames($altFirstNamesString);
        }

        if(is_null($existingActor->getAltSurnames()) && !is_null($newActor->getAltSurnames())){
            $existingActor->setAltSurnames($newActor->getAltSurnames());
        }
        else if(!is_null($existingActor->getAltSurnames()) && !is_null($newActor->getAltSurnames())){
            $oldAltSurnames = explode(", ", $existingActor->getAltSurnames());
            $newAltSurnames = explode(", ", $newActor->getAltSurnames());

            foreach($newAltSurnames as $surname){
                if(!in_array ( $surname, $oldAltSurnames)){
                    $oldAltSurnames[] = $surname;
                }
            }
            $altSurnamesString = "";
            foreach($oldAltSurnames as $surname){
                $altSurnamesString .= $surname . ", ";
            }
            $altSurnamesString = substr($altSurnamesString, 0, -2);
            $existingActor->setAltSurnames($altSurnamesString);
        }


        if(is_null($existingActor->getBirthdate()) && !is_null($newActor->getBirthdate())){
            $existingActor->setBirthdate($newActor->getBirthdate());
            $existingActor->setBirthdateAccuracy($newActor->getBirthdateAccuracy());
        }
        else if(!is_null($existingActor->getBirthdate()) && !is_null($newActor->getBirthdate())){
            if($existingActor->getBirthdateAccuracy()>$newActor->getBirthdateAccuracy()){
                $existingActor->setBirthdate($newActor->getBirthdate());
                $existingActor->setBirthdateAccuracy($newActor->getBirthdateAccuracy());
            }
        }

        if(is_null($existingActor->getDateOfDeath()) && !is_null($newActor->getDateOfDeath())){
            $existingActor->setDateOfDeath($newActor->getDateOfDeath());
            $existingActor->setDateOfDeathAccuracy($newActor->getDateOfDeathAccuracy());
        }
        else if(!is_null($existingActor->getDateOfDeath()) && !is_null($newActor->getDateOfDeath())){
            if($existingActor->getDateOfDeathAccuracy()>$newActor->getDateOfDeathAccuracy()){
                $existingActor->setDateOfDeath($newActor->getDateOfDeath());
                $existingActor->setDateOfDeathAccuracy($newActor->getDateOfDeathAccuracy());
            }
        }

        if(is_null($existingActor->getBirthPlace()) && !is_null($newActor->getBirthPlace())){
            $existingActor->setBirthPlace($newActor->getBirthPlace());
        }
        if(is_null($existingActor->getPlaceOfDeath()) && !is_null($newActor->getPlaceOfDeath())){
            $existingActor->setPlaceOfDeath($newActor->getPlaceOfDeath());
        }

        if(!is_null($newActor->getMentions())){
            foreach($newActor->getMentions() as $mention){
                $existingActor->addMention($mention);
            }
        }
        if(!is_null($newActor->getCorrespondent())) {
            if (!is_null($newActor->getCorrespondent()->getActions())) {
                foreach ($newActor->getCorrespondent()->getActions() as $action) {
                    $newActor->getCorrespondent()->removeAction($action);
                    $existingActor->getCorrespondent()->addAction($action);
                }
            }
        }

        if(!is_null($newActor->getPlaces())){
            foreach($newActor->getPlaces() as $place){
                $existingActor->addPlace($place);
            }
        }
        return $existingActor;
    }

    function mergeActorDuplicates(){
        $actorRepository = $this->entityManager->getRepository(Actor::class);
        $all_actors = $actorRepository->findAll();
        $fixed_actors = [];
        $foundDuplicates = false;
        foreach($all_actors as $actor){
            $fields = [];
            $fields['first_name'] = $actor->getFirstName();
            $fields['surname'] = $actor->getSurname();
            $fields['birthdate'] = $actor->getBirthdate();
            $fields['date_of_death'] = $actor->getDateOfDeath();
            $candidates = $actorRepository->findAnyByNamesAndDates($fields);
            if(count($candidates)>1 && !in_array($actor->getId(), $fixed_actors)){
                $foundDuplicates = true;
                echo count($candidates). " duplicates found for ". $actor ."<br>";
                $firstCandidate = reset($candidates);
                $fixed_actors[] = $firstCandidate->getId();
                $i = 0;
                foreach($candidates as $candidate){
                    if($i>0){
                        $firstCandidate = $this->merge($firstCandidate, $candidate);
                        $this->entityManager->remove($candidate);
                        $fixed_actors[] = $candidate->getId();
                    }
                    $i++;
                }
               $this->entityManager->persist($firstCandidate);
            }
        }
        if(!$foundDuplicates){
            echo "Found no duplicates!<br>";
        }
        else{
            $this->entityManager->flush();
        }
    }

    function hasUniqueSurname($actor){
        $fields['surname'] = $actor->getSurname();
        $actors = $this->actorRepository->findBy($fields);
        if(count($actors)>1)
            return false;
        else
            return true;
    }

    function getAllActorNames(){
        $allNames = [];
        $allActors =  $this->actorRepository->findAll();
        foreach($allActors as $actor){
            $allNames[] = $actor->getFirstName();
            $allNames[] = $actor->getSurname();
            array_merge($allNames, $actor->getAltFirstNamesArray());
            array_merge($allNames, $actor->getAltSurnamesArray());
        }
        $allNames = array_unique($allNames);
        return $allNames;
    }


}
?>
