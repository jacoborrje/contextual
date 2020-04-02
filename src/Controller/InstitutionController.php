<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 22:01
 */

namespace App\Controller;
use App\Utils\PlaceService;
use App\Utils\TimelineService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Institution;
use App\Entity\Correspondent;
use App\Form\InstitutionType;
use Symfony\Component\HttpFoundation\JsonResponse;



class InstitutionController extends AbstractController
{
    /**
    *   @Route("/institutions/index", name="institution_index")
    */
    public function index(Request $request){

        $repository = $this->getDoctrine()
            ->getRepository(Institution::class);

        $all_institutions = $repository->findAll();

        return $this->render('institution/index.html.twig', [
            'all_institutions' => $all_institutions
        ]);
    }


    /**
     *   @Route("/institution/view/{institution_id}", name="institution_view")
     */
    public function view($institution_id, TimelineService $timelineService){
        $repository = $this->getDoctrine()
            ->getRepository(Institution::class);
        $institution = $repository->find($institution_id);
        $timeline = $timelineService->createInstitutionTimeline($institution);


        return $this->render('institution/view.html.twig', [
            'institution' => $institution,
            'timeline' => $timeline
        ]);
    }

    /**
     *   @Route("/institution/edit/{institution_id}", name="institution_edit")
     */
    public function edit($institution_id, Request $request){
        $repository = $this->getDoctrine()
            ->getRepository(Institution::class);
        $institution = $repository->find($institution_id);

        $form = $this->createForm(InstitutionType::class, $institution);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $institution = $form->getData();
            $institution->setDateOfEstablishmentByString($institution->getTextDateOfEstablishment());
            $institution->setDateOfDissolutionByString($institution->getTextDateOfDissolution());

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($institution);
            $entityManager->flush();
        }

        return $this->render('institution/edit.html.twig', [
            'institution' => $institution,
            'form' => $form->createView()
        ]);
    }

    /**
     *   @Route("/institution/create", name="institution_create")
     */
    public function create(Request $request, PlaceService $placeService){
        $repository = $this->getDoctrine()
            ->getRepository(Institution::class);
        $institution = new Institution();

        $form = $this->createForm(InstitutionType::class, $institution);

        $form = $this->createForm(InstitutionType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $institution = $form->getData();

            $place = $institution->getPlace();
            if(is_null($place)){
                $newPlace = $form->get('new_place')->getData();
                if(!is_null($newPlace)){
                    echo "New Place:";
                    echo $newPlace->getName(); echo "<br>";
                    $newPlace = $placeService->refinePlace($newPlace);
                    $institution->setPlace($newPlace);
                }
            }

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            if(!is_null($institution->getTextDateOfEstablishment()))
                $institution->setDateOfEstablishmentByString($institution->getTextDateOfEstablishment());
            if(!is_null($institution->getTextDateOfDissolution()))
                $institution->setDateOfDissolutionByString($institution->getTextDateOfDissolution());

            if(!$institution->hasCorrespondent()){
                $correspondent = new Correspondent();
                $institution->setCorrespondent($correspondent);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($institution);
            $entityManager->flush();
            return $this->redirectToRoute('institution_edit', array('institution_id' => $institution->getId()));
        }

        return $this->render('institution/create.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     *   @Route("/institution/delete/{institution_id}", name="institution_delete")
     */
    public function delete($institution_id){
        $entityManager = $this->getDoctrine()->getManager();
        $institution = $entityManager->getRepository(Institution::class)->find($institution_id);

        if (!$institution) {
            throw $this->createNotFoundException(
                'No institution found for id '.$id
            );
        }

        $entityManager->remove($institution);
        $entityManager->flush();

        return $this->redirectToRoute('institution_index');
    }

    /**
     * @Route("/autocomplete/institution", name="institution_autocomplete")
     */
    public function autocompleteAction(Request $request)
    {
        $names = array();
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(Institution::class)->createQueryBuilder('i')
            ->where('i.name LIKE :name')
            ->setParameter('name', '%' . $term . '%')
            ->getQuery()
            ->getResult();

        foreach ($entities as $entity) {
            $newRow['id'] = $entity->getId();
            $newRow['name'] = $entity->getName();
            $names[] = $newRow;
        }

        $response = new JsonResponse();
        $response->setData($names);

        return $response;
    }
}