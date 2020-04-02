<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 22:01
 */

namespace App\Controller;
use App\Entity\MapOverlay;
use App\Entity\Timeline;
use App\Entity\TimelineEntry;
use App\Form\Type\CorrespondentAutocompleteType;
use App\Utils\Scrapers\AlvinPlaceScraper;
use App\Utils\TimelineService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Actor;
use App\Entity\Action;
use App\Entity\Occupation;
use App\Entity\Correspondent;
use App\Entity\Place;
use App\Entity\Relationship;
use App\Form\ActorType;
use App\Utils\Scrapers\AlvinActorScraper;
use App\Form\ImportURLType;
use Symfony\Component\HttpFoundation\JsonResponse;

class CorrespondentController extends AbstractController
{
    /**
     * @Route("/autocomplete/correspondent", name="correspondent_autocomplete")
     */
    public function autocompleteAction(Request $request)
    {
        $names = array();
        $term = trim(strip_tags($request->get('term')));
        $repository = $this->getDoctrine()
            ->getRepository(Correspondent::class);
        $entities = $repository->findByTerm($term);



        foreach ($entities as $entity)
        {
            $newRow['id'] = $entity->getId();
            if(!is_null($entity->getActor())){
                $newRow['type'] = 'actor';
                $newRow['name'] = $entity->getActor()->getFirstName()." ". $entity->getActor()->getSurname();
                $newRow['dates'] = ' ('.$entity->getActor()->getBirthYear()."&#8211;".$entity->getActor()->getDeathYear().")";
            }
            else if(!is_null($entity->getInstitution())){
                $newRow['type'] = 'institution';
                $newRow['name'] = $entity->getInstitution()->getName(). " | Institution";
                $newRow['dates'] = "";

            }
            $names[] = $newRow;
        }

        $response = new JsonResponse();
        $response->setData($names);

        return $response;
    }
}