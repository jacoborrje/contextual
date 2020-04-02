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

class RelationshipsController extends AbstractController
{
    /**
    *   @Route("/relationships/index", name="relationships_index")
    */
    public function index(){
        return $this->render('relationships/index.html.twig');

    }

}