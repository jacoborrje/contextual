<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 22:01
 */

namespace App\Controller;
use App\Utils\OAIScraperBase;
use App\Utils\Scrapers\AlvinActorScraper;
use App\Utils\Scrapers\AlvinSourceScraper;
use App\Utils\Scrapers\AlvinPlaceScraper;
use App\Utils\Scrapers\WikipediaActorScraperBase;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Actor;
use App\Repository\ActorRepository;

class TestController extends AbstractController
{
    /**
    *   @Route("/test/index", name="test_index")
    */
    public function index(){
        /*$repository = $this->getDoctrine()
            ->getRepository(Actor::class);
        $fields = ['surname'=>'Brander', 'first_name'=>'Charles'];
        $actor = $repository->findBy($fields);*/

        $url = "http://www.alvin-portal.org/oai/oai";
        $scraper = new AlvinSourceScraper($this->getDoctrine());
        $scraper->connect('20487');

        //$scraper = new WikipediaActorScraper($this->getDoctrine());
        //$scraper->connect('Carl_Linnaeus');

        //$scraper = new AlvinActorScraper($this->getDoctrine());
        //$scraper->connect(9151);

        //$scraper = new AlvinPlaceScraper($this->getDoctrine());
        //$scraper->connect(21);

        $source = $scraper->parse();

        return $this->render('test/index.html.twig', [
            'source' => $source
        ]);
    }
}