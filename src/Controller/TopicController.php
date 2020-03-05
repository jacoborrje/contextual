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
use App\Entity\Place;


class PlaceController extends AbstractController
{
    /**
    *   @Route("/places/index", name="place_index")
    */
    public function index(){

        $repository = $this->getDoctrine()
            ->getRepository(Place::class);

        $root_places = $repository->findAllRootPlaces();
        $all_places = $repository->findAll();

        return $this->render('place/index.html.twig', [
            'places' => $root_places,
            'all_places' => $all_places
        ]);
    }


    /**
     *   @Route("/archive/view/{archive_id}", name="archive_view")
     */
    public function view($archive_id){
        $repository = $this->getDoctrine()
            ->getRepository(Archive::class);
        $archive = $repository->find($archive_id);

        return $this->render('archive/view.html.twig', [
            'archive' => $archive,
        ]);
    }
}