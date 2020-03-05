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

class ActorController extends AbstractController
{
    /**
     * @Route("/actor/index", name="actor_index")
     */
    public function index(Request $request, AlvinActorScraper $scraper)
    {
        $repository = $this->getDoctrine()
            ->getRepository(Actor::class);

        $actors = $repository->findAll();

        $alvinImportForm = $this->createForm(ImportURLType::class);
        $alvinImportForm->handleRequest($request);

        if ($alvinImportForm->isSubmitted() && $alvinImportForm->isValid()) {
            $alvinID = $alvinImportForm->getData();
            $scraper->connect($alvinID->getURL());
            $actor = $scraper->parse();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($actor);
            $entityManager->flush();

            return $this->redirectToRoute('actor_index');
        }




        return $this->render('actor/index.html.twig', [
            'all_actors' => $actors,
            'alvinImportForm' =>$alvinImportForm->createView()

        ]);
    }


    /**
     * @Route("/actor/view/{actor_id}", name="actor_view")
     */
    public function view($actor_id, TimelineService $timelineService)
    {
        $repository = $this->getDoctrine()
            ->getRepository(Actor::class);
        $actor = $repository->find($actor_id);

        $mapRepository = $repository = $this->getDoctrine()
            ->getRepository(MapOverlay::class);
        $mapOverlays = $mapRepository->findAll();


        $timeline = $timelineService->createActorTimeline($actor);
        $geoTimeline = $timelineService->createActorTimeline($actor, false, false, true);

        return $this->render('actor/view.html.twig', [
            'actor' => $actor,
            'timeline' => $timeline,
            'geoTimeline' => $geoTimeline,
            'mapOverlays' => $mapOverlays,
        ]);
    }

    /**
     * @Route("/actor/edit/{actor_id}", name="actor_edit")
     */
    public function edit($actor_id, Request $request)
    {
        $repository = $this->getDoctrine()
            ->getRepository(Actor::class);
        $actor = $repository->find($actor_id);
        $form = $this->createForm(ActorType::class, $actor);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $actor = $form->getData();

            if($actor->getTextBirthdate(true) === ""){
                $actor->setBirthdate(null);
                $actor->setBirthdateAccuracy(null);
            }
            if($actor->getTextDateOfDeath(true) === "") {
                $actor->setDateOfDeath(null);
                $actor->setDateOfDeathAccuracy(null);
            }

            $actor->setBirthdateByString($actor->getTextBirthdate());
            $actor->setDateOfDeathByString($actor->getTextDateOfDeath());

            foreach($actor->getOccupations() as $occupation){
                $occupation->setStartDateByString($occupation->getTextStartDate());
                $occupation->setEndDateByString($occupation->getTextEndDate());
            }
            foreach($actor->getPlaces() as $place){
                $place->setDateOfArrivalByString($place->getTextDateOfArrival());
                $place->setDateOfLeavingByString($place->getTextDateOfLeaving());
            }
            foreach($actor->getPrimaryRelationships() as $relationship){
                $relationship->setStartDateByString($relationship->getTextStartDate());
                $relationship->setEndDateByString($relationship->getTextEndDate());
            }
            foreach($actor->getSecondaryRelationships() as $relationship){
                $relationship->setStartDateByString($relationship->getTextStartDate());
                $relationship->setEndDateByString($relationship->getTextEndDate());
            }
            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($actor);
            $entityManager->flush();

            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

        return $this->render('actor/edit.html.twig', [
            'actor' => $actor,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/actor/create", name="actor_create")
     */
    public function create(Request $request)
    {
        $repository = $this->getDoctrine()
            ->getRepository(Actor::class);
        $actor = new Actor();

        $form = $this->createForm(ActorType::class, $actor);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $actor = $form->getData();
            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            if(!is_null($actor->getTextBirthdate()))
                $actor->setBirthdateByString($actor->getTextBirthdate());
            if(!is_null($actor->getTextDateOfDeath()))
                $actor->setDateOfDeathByString($actor->getTextDateOfDeath());

            if(!$actor->hasCorrespondent()){
                $correspondent = new Correspondent();
                $actor->setCorrespondent($correspondent);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($actor);
            $entityManager->flush();
            return $this->redirectToRoute('actor_index');
        }
        return $this->render('actor/create.html.twig', [
            'actor' => $actor,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/autocomplete/actor", name="actor_autocomplete")
     */
    public function autocompleteAction(Request $request)
    {
        $names = array();
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(Actor::class)->createQueryBuilder('c')
            ->where('c.first_name LIKE :name OR c.surname LIKE :name OR YEAR(c.birthdate) LIKE :name OR YEAR(c.date_of_death) LIKE :name')
            ->setParameter('name', '%'.$term.'%')
            ->getQuery()
            ->getResult();

        foreach ($entities as $entity)
        {
            $names[] = $entity->getSurname(). ", ". $entity->getFirstName();
        }

        $response = new JsonResponse();
        $response->setData($names);

        return $response;
    }

}