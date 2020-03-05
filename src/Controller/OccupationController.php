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
use App\Entity\Topic;


class TopicController extends AbstractController
{
    /**
    *   @Route("/topics/index", name="topic_index")
    */
    public function index(){

        $repository = $this->getDoctrine()
            ->getRepository(Topic::class);

        $all_topics = $repository->findAll();

        return $this->render('topic/index.html.twig', [
            'all_topics' => $all_topics
        ]);
    }


    /**
     *   @Route("/archive/view/{topic_id}", name="topic_view")
     */
    public function view($topic_id){
        $repository = $this->getDoctrine()
            ->getRepository(Topic::class);
        $archive = $repository->find($topic_id);

        return $this->render('topic/view.html.twig', [
            'topic' => $topic,
        ]);
    }
}