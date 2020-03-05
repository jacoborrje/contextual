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
use App\Entity\Archive;
use App\Entity\Series;
use App\Form\ArchiveType;
use App\Form\SeriesType;


class ArchiveController extends AbstractController
{
    /**
    *   @Route("/archive/index", name="archive_index")
    */
    public function index(Request $request){

        $archive = new Archive();

        $repository = $this->getDoctrine()
            ->getRepository(Archive::class);

        $root_archives = $repository->findAllRootArchives();

        $form = $this->createForm(ArchiveType::class);


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

            return $this->redirectToRoute('archive_index');
        }

        return $this->render('archive/index.html.twig', [
            'archives' => $root_archives,
            'form' => $form->createView()
        ]);
    }


    /**
     *   @Route("/archive/view/{archive_id}", name="archive_view")
     */
    public function view($archive_id, Request $request){
        $repository = $this->getDoctrine()
            ->getRepository(Archive::class);
        $archive = $repository->find($archive_id);

        $child_archive = new Archive();
        $child_archive->setParent($archive);
        $child_archive->setPlace($archive->getPlace());
        $child_series = new Series();
        $child_series->setArchive($archive);

        $childArchiveForm = $this->createForm(ArchiveType::class, $child_archive);
        $childSeriesForm = $this->createForm(SeriesType::class, $child_series);

        $childArchiveForm->handleRequest($request);

        if ($childArchiveForm->isSubmitted() && $childArchiveForm->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $child_archive = $childArchiveForm->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($child_archive);
            $entityManager->flush();

            return $this->redirectToRoute('archive_view', array('archive_id' => $archive_id));
        }

        $childSeriesForm->handleRequest($request);

        if ($childSeriesForm->isSubmitted() && $childSeriesForm->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $child_series = $childSeriesForm->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($child_series);
            $entityManager->flush();

            return $this->redirectToRoute('archive_view', array('archive_id' => $archive_id));
        }


        return $this->render('archive/view.html.twig', [
            'archive' => $archive,
            'childArchiveForm' => $childArchiveForm->createView(),
            'childSeriesForm' => $childSeriesForm->createView()
        ]);
    }

    /**
     *   @Route("/archive/delete/{archive_id}", name="archive_delete")
     */
    public function delete($archive_id){
        $entityManager = $this->getDoctrine()->getManager();
        $archive = $entityManager->getRepository(Archive::class)->find($archive_id);

        if (!$archive) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $entityManager->remove($archive);
        $entityManager->flush();

        return $this->redirectToRoute('archive_index');
    }

    /**
     *   @Route("/archive/edit/{archive_id}", name="archive_edit")
     */
    public function edit($archive_id, Request $request){
        $repository = $this->getDoctrine()
            ->getRepository(Archive::class);
        $archive = $repository->find($archive_id);

        $form = $this->createForm(ArchiveType::class, $archive);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $archive = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($archive);
            $entityManager->flush();
        }


        return $this->render('archive/edit.html.twig', [
            'archive' => $archive,
            'form' => $form->createView()
        ]);
    }
}