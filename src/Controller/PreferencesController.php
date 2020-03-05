<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 22:01
 */

namespace App\Controller;
use App\Form\ImportURLType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Source;
use App\Entity\Series;
use App\Entity\Volume;
use App\Form\SourceType;
use App\Form\VolumeType;
use App\Entity\ImportURL;
use App\Utils\Scrapers\AlvinSourceScraper;

class VolumeController extends AbstractController
{
    /**
    *   @Route("/volume/index", name="volume_index")
    */
    public function index(){

    }


    /**
     *   @Route("/volume/view/{volume_id}", name="volume_view")
     */
    public function view($volume_id, Request $request, AlvinSourceScraper $scraper){
        $repository = $this->getDoctrine()
            ->getRepository(Volume::class);
        $volume = $repository->find($volume_id);

         if ($volume_id == 863){
             $is_alvin = true;
         }
         else{
             $is_alvin = false;
         }

        $child_source = new Source();
        $child_source->setVolume($volume);

        $form = $this->createForm(SourceType::class, $child_source);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $source = $form->getData();
            $source->setDateByString($source->getTextDate());

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($source);
            $entityManager->flush();

            return $this->redirectToRoute('volume_view', array('volume_id' => $volume_id));
        }

        if($is_alvin){
            $alvinImportForm = $this->createForm(ImportURLType::class);
            $alvinImportForm->handleRequest($request);

            if ($alvinImportForm->isSubmitted() && $alvinImportForm->isValid()) {
                $alvinID = $alvinImportForm->getData();
                $scraper->connect($alvinID->getURL());
                $source = $scraper->parse();

                $entityManager = $this->getDoctrine()->getManager();
                //$entityManager->persist($source);
                //$entityManager->flush();

                //return $this->redirectToRoute('volume_view', array('volume_id' => $volume_id));
            }

            return $this->render('volume/view.html.twig', [
                'volume' => $volume,
                'form' => $form->createView(),
                'alvinImportForm' =>$alvinImportForm->createView()
            ]);
        }


        return $this->render('volume/view.html.twig', [
            'volume' => $volume,
            'form' => $form->createView(),
        ]);
    }

    /**
     *   @Route("/volume/delete/{volume_id}", name="volume_delete")
     */
    public function delete($volume_id, Request $request){
        $entityManager = $this->getDoctrine()->getManager();
        $volume = $entityManager->getRepository(Volume::class)->find($volume_id);

        if (!$volume) {
            throw $this->createNotFoundException(
                'No volume found for id '.$volume_id
            );
        }

        $entityManager->remove($volume);
        $entityManager->flush();

        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    /**
     *   @Route("/volume/edit/{volume_id}", name="volume_edit")
     */
    public function edit($volume_id, Request $request){
        $repository = $this->getDoctrine()
            ->getRepository(Volume::class);
        $volume = $repository->find($volume_id);
        $form = $this->createForm(VolumeType::class, $volume);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $volume = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($volume);
            $entityManager->flush();
        }

        return $this->render('volume/edit.html.twig', [
            'volume' => $volume,
            'form' => $form->createView()
        ]);
    }

}