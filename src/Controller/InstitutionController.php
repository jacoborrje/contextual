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
use App\Entity\Occupation;


class OccupationController extends AbstractController
{
    /**
    *   @Route("/occupations/index", name="occupation_index")
    */
    public function index(){

        $repository = $this->getDoctrine()
            ->getRepository(Occupation::class);

        $all_occupations = $repository->findAll();

        return $this->render('occupation/index.html.twig', [
            'all_occupations' => $all_occupations
        ]);
    }


    /**
     *   @Route("/archive/view/{occupation_id}", name="occupation_view")
     */
    public function view($occupation_id){
        $repository = $this->getDoctrine()
            ->getRepository(Topic::class);
        $archive = $repository->find($occupation_id);

        return $this->render('occupation/view.html.twig', [
            'occupation' => $occupation,
        ]);
    }
}