<?php
namespace App\Utils;

use \App\Entity\Actor;
use App\Entity\Correspondent;
use Doctrine\ORM\EntityManagerInterface;


class CorrespondentService
{

    protected $entityManager;

    function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    function purgeDuplicateCorrespondents(){
        $correspondentRepository = $this->entityManager->getRepository(Correspondent::class);
        $allCorrespondents = $correspondentRepository->findAll();
        $removedDuplicates = 0;
        $removedCorrespondents = [];

        foreach($allCorrespondents as $correspondent){
            if(!is_null($correspondent->getActor())){
                $duplicates = $correspondentRepository->findBy(array('actor' => $correspondent->getActor()->getId()));
            }
            else{
                $duplicates = $correspondentRepository->findBy(array('institution' => $correspondent->getInstitution()->getId()));
            }

            if(count($duplicates)>1) {
                echo "Found duplicates for correspondent " . $correspondent . "(" . count($duplicates) . ") Actor id: " . $correspondent->getActor()->getId() . "<br>";

                $original = $duplicates[0];
                $first = true;

                if (!in_array($original->getId(), $removedCorrespondents)) {
                    foreach ($duplicates as $duplicate) {
                        if (!$first) {
                            foreach ($duplicate->getActions() as $action) {
                                $action->setCorrespondent($original);
                                $original->addAction($action);
                            }
                            $removedCorrespondents[] = $duplicate->getId();
                            $this->entityManager->remove($duplicate);
                            $removedDuplicates++;
                        } else {
                            $first = false;
                        }
                    }
                }
            }
        }
        $this->entityManager->flush();
        echo "Removed ".$removedDuplicates." duplicates.";
    }
}
?>