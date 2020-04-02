<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 22:01
 */

namespace App\Controller;
use App\Entity\MapOverlay;
use App\Entity\Source;
use App\Utils\ActorService;
use App\Utils\Analysis\TextAnalysisService;
use App\Utils\CorrespondentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Analysis\TopicService;

class PreferencesController extends AbstractController
{
    /**
    *   @Route("/preferences/index", name="preferences_index")
    */
    public function index(){
        $repository = $this->getDoctrine()
            ->getRepository(MapOverlay::class);

        $mapOverlays = $repository->findAll();


        return $this->render('preferences/index.html.twig', [
                'mapOverlays' => $mapOverlays
            ]
        );
    }

    /**
     *   @Route("/preferences/retrainTopicModels", name="preferences_retrainTopicModels")
     */
    public function retrainTopicModels(TopicService $topicService){
        $topicService->trainModels();
        return $this->redirectToRoute('preferences_index');
    }

    /**
     *   @Route("/preferences/generateStopwordSuggestions", name="preferences_generateStopwordSuggestions")
     */
    public function generateStopwordSuggestions(TextAnalysisService $textAnalysisService){
        $textAnalysisService->generateStopwordSuggestions();
        return $this->redirectToRoute('preferences_index');
    }

    /**
     *   @Route("/preferences/generateLemmatizationDraft", name="preferences_generateLemmatizationDraft")
     */
    public function generateLemmatizationDraft(TextAnalysisService $textAnalysisService){
        $textAnalysisService->generateLemmatizationDraft();
        return $this->redirectToRoute('preferences_index');
    }


    /**
     *   @Route("/preferences/mergeActorDuplicates", name="preferences_mergeActorDuplicates")
     */
    public function mergeActorDuplicated(ActorService $actorService){
        $actorService->mergeActorDuplicates();
        return $this->redirectToRoute('preferences_index');
    }

    /**
     *   @Route("/preferences/purgeDuplicateCorrespondents", name="preferences_purgeDuplicateCorrespondents")
     */
    public function purgeDuplicateCorrespondents(CorrespondentService $correspondentService){
        $correspondentService->purgeDuplicateCorrespondents();
        //return $this->redirectToRoute('preferences_index');
    }

    /**
     *   @Route("/preferences/decodeHTMLEntitiesInDatabase", name="preferences_decodeHTMLEntitiesInDatabase")
     */
    public function decodeHTMLEntitiesInDatabase(CorrespondentService $correspondentService){
        $sourceRepository = $this->getDoctrine()
            ->getRepository(Source::class);

        $allSources = $sourceRepository->findAll();
        $entityManager = $this->getDoctrine()->getManager();

        foreach($allSources as $source){
            if($source->getTranscription() !== "") {
                $source->setTranscription(html_entity_decode($source->getTranscription()));
                $entityManager->persist($source);

            }
        }
        $entityManager->flush();
        return $this->redirectToRoute('preferences_index');
    }


}


