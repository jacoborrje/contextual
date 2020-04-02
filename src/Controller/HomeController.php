<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 22:01
 */

namespace App\Controller;
use App\Form\ActorType;
use App\Form\SearchQueryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Source;
use App\Entity\Actor;
use App\Entity\Place;
use App\Entity\Volume;



class HomeController extends AbstractController
{
    /**
    *   @Route("/", name="home_index")
    */
    public function index(Request $request){
        $sourceRepository = $this->getDoctrine()
            ->getRepository(Source::class);

        $actorRepository = $this->getDoctrine()
            ->getRepository(Actor::class);

        $placeRepository = $this->getDoctrine()
            ->getRepository(Place::class);

        $volumeRepository = $this->getDoctrine()
            ->getRepository(Volume::class);

        $lastSources = $sourceRepository->findLastEdited(5);
        $lastActors = $actorRepository->findLastEdited(5);
        $lastPlaces = $placeRepository->findLastEdited(5);
        $lastVolumes = $volumeRepository->findLastEdited(5);


        $form = $this->createForm(SearchQueryType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

        }

        return $this->render('home/index.html.twig', [
            'lastSources' => $lastSources,
            'lastActors' => $lastActors,
            'lastPlaces' => $lastPlaces,
            'lastVolumes' => $lastVolumes
        ]);
    }
}