<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 22:01
 */

namespace App\Controller;
use App\Entity\ImportURL;
use App\Form\ImportURLType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Series;
use App\Entity\Volume;
use App\Form\SeriesType;
use App\Form\VolumeType;
use App\Utils\Scrapers\RiksarkivetScraper;


class SeriesController extends AbstractController
{
    /**
    *   @Route("/series/index", name="series_index")
    */
    public function index(){

        $repository = $this->getDoctrine()
            ->getRepository(Series::class);

        $root_series = $repository->findAllRootSeries();

        return $this->render('series/index.html.twig', [
            'series' => $root_series,
        ]);
    }


    /**
     *   @Route("/series/view/{series_id}", name="series_view")
     */
    public function view($series_id, Request $request){
        $repository = $this->getDoctrine()
            ->getRepository(Series::class);
        $series = $repository->find($series_id);

        $child_series = new Series();
        $child_series->setParent($series);
        $form = $this->createForm(SeriesType::class, $child_series);

        $child_volume = new Volume();
        $child_volume->setSeries($series);
        $volumeForm = $this->createForm(VolumeType::class, $child_volume);

        $NADForm = $this->createForm(ImportURLType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $child_series = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($child_series);
            $entityManager->flush();

            return $this->redirectToRoute('series_view', array('series_id' => $series_id));
        }

        $volumeForm->handleRequest($request);
        if ($volumeForm->isSubmitted() && $volumeForm->isValid()) {
            $child_volume = $volumeForm->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($child_volume);
            $entityManager->flush();

            return $this->redirectToRoute('series_view', array('series_id' => $series_id));
        }

        $NADForm->handleRequest($request);
        if ($NADForm->isSubmitted() && $NADForm->isValid()) {
            $importURL = $NADForm->getData();
            $scraper = new RiksarkivetScraper();
            $scraper->connect($importURL->getUrl());
            $rows = $scraper->parse();
            $entityManager = $this->getDoctrine()->getManager();
            if(!is_null($rows)) {
                foreach ($rows as $row) {
                    $volume = new Volume();
                    if(isset($row['name']))
                        $volume->setName($row['name']);
                    else{
                        if(isset($row['start_date'])&&isset($row['end_date'])){
                            $volume->setName($row['start_date']. "–". $row['end_date']);
                        }
                        else if(isset($row['start_date'])){
                            $volume->setName($row['start_date']);
                        }
                        else if(isset($row['end_date'])){
                            $volume->setName($row['end_date']);
                        }
                        else if(isset(($row['abbreviation']))){
                            $volume->setName($row['abbreviation']);
                        }
                    }
                    if(isset($row['abbreviation']))
                        $volume->setAbbreviation($row['abbreviation']);
                    if(isset($row['start_date']))
                        $volume->setStartDateByString($row['start_date']);
                    if(isset($row['end_date']))
                        $volume->setEndDateByString($row['end_date']);
                    $volume->setSeries($series);
                    $entityManager->persist($volume);
                    $entityManager->flush();
                }
            }
            return $this->redirectToRoute('series_view', array('series_id' => $series_id));
        }


        return $this->render('series/view.html.twig', [
            'series' => $series,
            'childForm' => $form->createView(),
            'volumeForm' => $volumeForm->createView(),
            'NADForm' => $NADForm->createView()
        ]);
    }

    /**
     *   @Route("/series/delete/{series_id}", name="series_delete")
     */
    public function delete($series_id, Request $request){
        $entityManager = $this->getDoctrine()->getManager();
        $series = $entityManager->getRepository(Series::class)->find($series_id);

        if (!$series) {
            throw $this->createNotFoundException(
                'No series found for id '.$id
            );
        }

        $entityManager->remove($series);
        $entityManager->flush();

        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    /**
     *   @Route("/series/edit/{series_id}", name="series_edit")
     */
    public function edit($series_id, Request $request){
        $repository = $this->getDoctrine()
            ->getRepository(Series::class);
        $series = $repository->find($series_id);

        $form = $this->createForm(SeriesType::class, $series);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $series = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($series);
            $entityManager->flush();
        }
        return $this->render('series/edit.html.twig', [
            'series' => $series,
            'form' => $form->createView()
        ]);
    }

}