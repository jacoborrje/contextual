<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 22:01
 */

namespace App\Controller;
use App\Kernel;
use App\Utils\FileUploader;
use App\Utils\GeographyHelper;
use App\Utils\ImageService;
use App\Utils\PlaceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Place;
use App\Entity\DatabaseFile;
use App\Form\PlaceType;
use Symfony\Component\HttpFoundation\JsonResponse;

class PlaceController extends AbstractController
{
    /**
     * @Route("/places/index", name="place_index")
     */
    public function index(Request $request)
    {

        $repository = $this->getDoctrine()
            ->getRepository(Place::class);

        $root_places = $repository->findAllRootPlaces();
        $all_places = $repository->findAll();

        $form = $this->createForm(PlaceType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $place = $form->getData();
            $place->setGeoData();
            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($place);
            $entityManager->flush();

            return $this->redirectToRoute('place_index');
        }

        return $this->render('place/index.html.twig', [
            'places' => $root_places,
            'form' => $form->createView(),
            'all_places' => $all_places
        ]);

    }


    /**
     * @Route("/place/view/{place_id}", name="place_view")
     */
    public function view($place_id)
    {
        $repository = $this->getDoctrine()
            ->getRepository(Place::class);
        $place = $repository->find($place_id);

        return $this->render('place/view.html.twig', [
            'place' => $place,
        ]);
    }

    /**
     * @Route("/place/delete/{place_id}", name="place_delete")
     */
    public function delete($place_id, FileUploader $fileUploader)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $place = $entityManager->getRepository(Place::class)->find($place_id);

        if (!$place) {
            throw $this->createNotFoundException(
                'No place found for id ' . $id
            );
        }
        if(!is_null($place->getImage()))
            $place = $fileUploader->removePlaceImage($place, $place->getImage());

        $entityManager->remove($place);
        $entityManager->flush();

        return $this->redirectToRoute('place_index');
    }


    /**
     * @Route("/place/create/{parent_id}", name="place_create")
     */
    public function create($parent_id = null, Request $request, PlaceService $placeService, FileUploader $fileUploader)
    {
        $repository = $this->getDoctrine()
            ->getRepository(Place::class);
        $place = new Place();
        if(!is_null($parent_id)) {
            $parent = $repository->find($parent_id);
            $place->setParent($parent);
        }

        $form = $this->createForm(PlaceType::class, $place);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $place = $form->getData();
            $place = $placeService->refinePlace($place);

            if(is_null($place->getImage()->getFileContents())) {
                $place->setImage(null);
            }
            else {
                $place = $fileUploader->uploadPlaceImage($place);
                if(is_null($place->getImage()->getType())){
                    $place->setImage(null);
                }
            }

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($place);
            $entityManager->flush();
            return  $this->redirectToRoute('place_edit', array('place_id' => $place->getId()));
        }

        return $this->render('place/create.html.twig', [
            'place' => $place,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/place/edit/{place_id}", name="place_edit")
     */
    public function edit($place_id, Request $request, ImageService $imageService, FileUploader $fileUploader)
    {
        $repository = $this->getDoctrine()
            ->getRepository(Place::class);
        $place = $repository->find($place_id);
        $original_image = $place->getImage();

        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $place = $form->getData();

            if(is_null($place->getImage()->getFileContents()) && !is_null($original_image))
                $place->setImage($original_image);
            else if (!is_null($place->getImage()->getFileContents())){
                $place = $fileUploader->uploadPlaceImage($place);
            }
            else{
                $place->setImage(null);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($place);
            $entityManager->flush();
        }
        else{
            echo $form->getErrors();
        }


        return $this->render('place/edit.html.twig', [
            'place' => $place,
            'form' => $form->createView()
        ]);
    }


    /**
     *   @Route("/place/removeimage/{place_id}/{image_id}", name="place_remove_image")
     */
    public function removeImage($place_id, $image_id, Request $request, ImageService $imageService, FileUploader $fileUploader){
        $file_repository = $this->getDoctrine()
            ->getRepository(Place::class);
        $place = $file_repository->find($place_id);
        $image = $place->getImage();
        $place = $fileUploader->removePlaceImage($place, $image);

        return  $this->redirectToRoute('place_edit', array('place_id' => $place->getId()));
    }


    /**
     * @Route("/autocomplete/place", name="place_autocomplete")
     */
    public function autocompleteAction(Request $request)
    {
        $names = array();
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(Place::class)->createQueryBuilder('c')
            ->where('c.name LIKE :name')
            ->setParameter('name', '%' . $term . '%')
            ->getQuery()
            ->getResult();

        foreach ($entities as $entity) {
            $newRow['id'] = $entity->getId();
            $newRow['name'] = $entity->getName();
            if(!is_null($entity->getParent())) {
                $newRow['parentName'] = $entity->getParent()->getName();
            }
            else{
                $newRow['parentName'] = "";
            }
            $newRow['type'] = $entity->getTypeString();

            $names[] = $newRow;
        }

        $response = new JsonResponse();
        $response->setData($names);

        return $response;
    }

    /**
     * @Route("geojson/index", name="place_geojson_index",
     *     options = { "expose" = true },
     *     )
     */
    public function geoJsonIndex(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $placeRepository = $em->getRepository(Place::class);
        $all_places = $placeRepository->findAll();
        $featuresJson = [];
        foreach ($all_places as $place) {
            $url = $this->generateUrl('place_edit', array('place_id' => $place->getId()));
            $description = '<a href="'.$url.'">'.(string) $place.'</a>';
            if($place->hasParent())
                $description .= " in ".$place->getParent();

            $featuresJson[] = [     'type'     => 'Feature',
                                    'geometry' =>
                                    [
                                        'type' => 'Point',
                                        'coordinates' => [$place->getLng(), $place->getLat()]
                                    ],
                                    'properties' =>
                                    [
                                         'title' => 'Mapbox',
                                         'description' => $description,
                                         'id' => $place->getId()
                                    ]
                               ];
        }
        $geoJson[] = ['type' => 'FeatureCollection',
        'features' => $featuresJson];
        $response = new JsonResponse();
        $response->setData($geoJson);

        return $response;
    }

    /**
     * @Route("/geojson/children/{place_id}", name="place_geojson_children",
     *     options = { "expose" = true },
     * )
     */
    public function geoJsonChildren($place_id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $placeRepository = $em->getRepository(Place::class);
        $all_places = $placeRepository->findChildren($place_id);
        $featuresJson = [];
        foreach ($all_places as $place) {
            $url = $url = $this->generateUrl('place_view', array('place_id' => $place->getId()));
            $description = "";
            if(!is_null($place->getImage())) {
                $image_url = $this->container->get('assets.packages')->getUrl('placeimages/'.$place->getImage()->getName());
                $description .= '<img src="' .$image_url.'">';
            }
            $description .= '<a href="'.$url.'">'.(string) $place.'</a>';
            if($place->hasParent())
                $description .= " in ".$place->getParent();

            $featuresJson[] = [     'type'     => 'Feature',
                'geometry' =>
                    [
                        'type' => 'Point',
                        'coordinates' => [$place->getLng(), $place->getLat()]
                    ],
                'properties' =>
                    [
                        'title' => 'Mapbox',
                        'description' => $description,
                        'id' => $place->getId()
                    ]
            ];
        }
        $geoJson[] = ['type' => 'FeatureCollection',
            'features' => $featuresJson];
        $response = new JsonResponse();
        $response->setData($geoJson);

        return $response;
    }

    /**
     * @Route("/geojson/decendants/{place_id}", name="place_geojson_decendants",
     *     options = { "expose" = true },
     * )
     */
    public function geoJsonDecendants($place_id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $placeRepository = $em->getRepository(Place::class);
        $place = $placeRepository->find($place_id);
        $all_places = $place->getDecendants();
        $featuresJson = [];
        foreach ($all_places as $place) {
            $url = $this->generateUrl('place_view', array('place_id' => $place->getId()));
            $description = "";
            if(!is_null($place->getImage())) {
                $image_url = $request->getBasePath() . '/placeimages/'.$place->getImage()->getThumbnailName();
                $description .= '<img src="' .$image_url.'">';
            }
            $description .= "<div><a href='".$url."'><h3>". $place."</h3></a>";
            if($place->hasParent())
                $description .= "Parent: ".$place->getParent();

            $description .= "</div>";
            $featuresJson[] = [     'type'     => 'Feature',
                'geometry' =>
                    [
                        'type' => 'Point',
                        'coordinates' => [$place->getLng(), $place->getLat()]
                    ],
                'properties' =>
                    [
                        'title' => 'Mapbox',
                        'description' => $description,
                        'id' => $place->getId()
                    ]
            ];
        }
        $geoJson[] = ['type' => 'FeatureCollection',
            'features' => $featuresJson];
        $response = new JsonResponse();
        $response->setData($geoJson);

        return $response;



    }


    /**
     * @Route("/geojson/view/{place_id}", name="place_geojson_view",
     *     options = { "expose" = true },
     * )
     */
    public function geoJsonView($place_id, Request $request){
        $em = $this->getDoctrine()->getManager();
        $placeRepository = $em->getRepository(Place::class);
        $place = $placeRepository->find($place_id);
        $featuresJson = [];
        $url = $this->generateUrl('place_edit', array('place_id' => $place->getId()));
        $description = '<a href="'.$url.'">'.(string) $place.'</a>';
        $featuresJson[] = [     'type'     => 'Feature',
            'geometry' =>
                [
                    'type' => 'Point',
                    'coordinates' => [$place->getLng(), $place->getLat()]
                ],
            'properties' =>
                [
                    'title' => 'Mapbox',
                    'description' => $description,
                    'id' => $place->getId()
                ]
        ];
        $geoJson[] = ['type' => 'FeatureCollection',
            'features' => $featuresJson];
        $response = new JsonResponse();
        $response->setData($geoJson);

        return $response;
    }

    /**
     * @Route("/json/place/overlay/{place_id}", name="place_json_mapoverlay",
     *     options = { "expose" = true },
     * )
     */
    public function jsonMapOverlay($place_id, Request $request){
        $em = $this->getDoctrine()->getManager();
        $placeRepository = $em->getRepository(Place::class);
        $place = $placeRepository->find($place_id);
        $json = [];
        $overlays = $place->getMapOverlays();

        if(!is_null($overlays)) {
            foreach ($overlays as $overlay) {
                $json[] = ['Name' => $overlay->getName(), 'url' => $overlay->getUrl(), 'coordinates' => [$place->getLng(), $place->getLat()], 'zoom' => '14'];
            }
        }
        $response = new JsonResponse();
        $response->setData($json);
        return $response;
    }




}