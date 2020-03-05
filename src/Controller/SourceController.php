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
use App\Entity\Actor;

class ActorController extends AbstractController
{
    /**
    *   @Route("/actor/index", name="actor_index")
    */
    public function index(){

        $repository = $this->getDoctrine()
            ->getRepository(Actor::class);

        $actors =$repository->findAll();

        return $this->render('actor/index.html.twig', [
            'all_actors' => $actors,
        ]);
    }


    /**
     *   @Route("/actor/view/{actor_id}", name="actor_view")
     */
    public function view($actor_id){
        $repository = $this->getDoctrine()
            ->getRepository(Actor::class);
        $actor = $repository->find($actor_id);

        return $this->render('actor/view.html.twig', [
            'actor' => $actor,
        ]);
    }
}