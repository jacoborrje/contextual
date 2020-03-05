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
use App\Entity\Series;

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
    public function view($series_id){
        $repository = $this->getDoctrine()
            ->getRepository(Series::class);
        $series = $repository->find($series_id);

        return $this->render('series/view.html.twig', [
            'series' => $series,
        ]);
    }
}