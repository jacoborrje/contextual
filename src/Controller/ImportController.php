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

class PreferencesController extends AbstractController
{
    /**
    *   @Route("/preferences/index", name="preferences_index")
    */
    public function index(){
        return $this->render('preferences/index.html.twig');

    }

}