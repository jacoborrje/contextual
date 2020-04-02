<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 22:01
 */

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Occupation;
use App\Form\OccupationType;
use Symfony\Component\HttpFoundation\JsonResponse;



class OccupationController extends AbstractController
{
    /**
    *   @Route("/occupations/index", name="occupation_index")
    */
    public function index(Request $request){

        $repository = $this->getDoctrine()
            ->getRepository(Occupation::class);

        $all_occupations = $repository->findAll();


        $form = $this->createForm(OccupationType::class);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('occupation_index');
        }

        return $this->render('occupation/index.html.twig', [
            'all_occupations' => $all_occupations,
            'form' => $form->createView()
        ]);
    }


    /**
     *   @Route("/occupation/view/{occupation_id}", name="occupation_view")
     */
    public function view($occupation_id){
        $repository = $this->getDoctrine()
            ->getRepository(Occupation::class);
        $occupation = $repository->find($occupation_id);

        return $this->render('occupation/view.html.twig', [
            'occupation' => $occupation,
        ]);
    }

    /**
     *   @Route("/occupation/edit/{occupation_id}", name="occupation_edit")
     */
    public function edit($occupation_id, Request $request){
        $repository = $this->getDoctrine()
            ->getRepository(Occupation::class);
        $occupation = $repository->find($occupation_id);

        $form = $this->createForm(OccupationType::class, $occupation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $occupation = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($occupation);
            $entityManager->flush();
        }

        return $this->render('occupation/edit.html.twig', [
            'occupation' => $occupation,
            'form' => $form->createView()
        ]);
    }

    /**
     *   @Route("/occupation/delete/{occupation_id}", name="occupation_delete")
     */
    public function delete($occupation_id){
        $entityManager = $this->getDoctrine()->getManager();
        $occupation = $entityManager->getRepository(Occupation::class)->find($occupation_id);

        if (!$occupation) {
            throw $this->createNotFoundException(
                'No occupation found for id '.$id
            );
        }

        $entityManager->remove($occupation);
        $entityManager->flush();

        return $this->redirectToRoute('occupation_index');
    }

    /**
     * @Route("/autocomplete/occupation", name="occupation_autocomplete")
     */
    public function autocompleteAction(Request $request)
    {
        $names = array();
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(Occupation::class)->createQueryBuilder('i')
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