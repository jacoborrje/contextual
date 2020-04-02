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
use App\Entity\SearchQuery;

class SearchController extends AbstractController
{
    /**
     *   @Route("/search/", name="search")
     */
    public function search(Request $request)
    {
        $sourceRepository = $this->getDoctrine()
            ->getRepository(Source::class);


        $form = $this->createForm(SearchQueryType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $search_query = $form->getData();
            $foundSources = $sourceRepository->findFulltext($search_query);
        }
        else{
            $search_query = "";
            $foundSources = null;
        }

        return $this->render('search/search.html.twig', [
            'foundSources' => $foundSources,
            'searchQuery' => $search_query,
            'form' => $form->createView()
        ]);
    }
}