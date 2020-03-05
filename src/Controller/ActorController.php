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
use App\Entity\Archive;

class ArchiveController extends AbstractController
{
    /**
    *   @Route("/archive/index", name="Archive index")
    */
    public function index(){

        $repository = $this->getDoctrine()
            ->getRepository(Archive::class);

        $root_archives =$repository->findAllRootArchives();

        return $this->render('archive/index.html.twig', [
            'archives' => $root_archives,
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