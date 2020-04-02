<?php

/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-01-10
 * Time: 22:01
 */

namespace App\Controller;
use App\Entity\DatabaseFile;
use App\Entity\Mention;
use App\Utils\Analysis\TopicService;
use App\Utils\EntitiesParser;
use App\Utils\FileUploader;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Source;
use App\Entity\Place;
use App\Entity\Action;
use App\Entity\Actor;
use App\Form\SourceType;
use App\Form\ActionType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\FoundActorType;
use App\Form\FoundPlaceType;
use App\Form\NewSourceTopicType;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Form\Type\VichFileType as UploadedFile;



class SourceController extends AbstractController
{
    /**
    *   @Route("/source/index", name="source_index")
    */
    public function index(){

        $repository = $this->getDoctrine()
            ->getRepository(Source::class);

        $sources =$repository->findAll();

        return $this->render('source/index.html.twig', [
            'all_sources' => $sources,
        ]);
    }

    /**
     *   @Route("/source/view/{source_id}", name="source_view")
     */
    public function view($source_id){
        $repository = $this->getDoctrine()
            ->getRepository(Source::class);
        $source = $repository->find($source_id);

        return $this->render('source/view.html.twig', [
            'source' => $source,
        ]);
    }

    /**
     *   @Route("/source/edit/{source_id}/{topicSuggestions}", name="source_edit")
     */
    public function edit($source_id, $topicSuggestions = false, Request $request, FileUploader $fileUploader, LoggerInterface $logger, TopicService $topicService)
    {
        $logger->warning('Editing source.');
        $repository = $this->getDoctrine()
            ->getRepository(Source::class);
        $source = $repository->find($source_id);
        $volume = $source->getVolume();
        $previousSource = null;
        $nextSource = null;
        $siblings = $volume->getSources();
        if ($topicSuggestions) {
            if ($source->getTranscription() !== "" && !is_null($source->getTranscription())) {
                $suggestedTopics = $topicService->makeTopicSuggestions($source);
            } else {
                $suggestedTopics = [];
            }
        }
        else{
            $suggestedTopics = [];
        }

        foreach ($siblings as $key => $sibling){
            if($source->getId() === $sibling->getId()){
                if($key>0){
                    $previousSource = $siblings[$key-1];
                }
                if($key<count($siblings)){
                    $nextSource = $siblings[$key+1];
                }
            }
        }

        $form = $this->createForm(SourceType::class, $source);
        $newTopicForm = $this->createForm(NewSourceTopicType::class);

        if(!is_null($source->getFiles()) && count($source->getFiles())!==0) {
            //echo "Found an original file<br>";
            $original_file = $source->getFile();
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $source = $form->getData();
            if($source->getTranscription() !== "") {
                $source->setTranscription(html_entity_decode($source->getTranscription()));
            }
            if($source->getTextDate() === "") {
                $source->setDate(null);
                $source->setDateAccuracy(null);
            }
            $source->setDateByString($source->getTextDate());
            foreach($source->getActions() as $action){
                if($action->getTextStartDate() === "") {
                    $action->setStartDate(null);
                    $action->setStartDateAccuracy(null);
                }
                if($action->getTextEndDate() === "") {
                    $action->setEndDate(null);
                    $action->setEndDateAccuracy(null);
                }
                $action->setStartDateByString($action->getTextStartDate());
                $action->setEndDateByString($action->getTextEndDate());
            }
            foreach($source->getMentions() as $mention){
                if($mention->getTextDate() === ""){
                    $mention->setDate(null);
                    $mention->setDateAccuracy(null);
                }
                $mention->setDateByString($mention->getTextDate());
            }

            if(!is_null($source->getFile()->getFileContents())&&count($source->getFile()->getFileContents())!==0) {
                echo "Found a file!";
                $source = $fileUploader->uploadSourcePdf($source);
            }
            else if((count($source->getFile()->getFileContents())===0)&&isset($original_file)) {
                //echo "Retaining original file<br>";
                $source->setFile($original_file);
            }
            else {
                //echo "Found no files at all!";
                $source->setFiles(null);
            }

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($source);
            $entityManager->flush();
            return $this->redirectToRoute('source_edit', ['source_id'=>$source->getId()]);
        }

        return $this->render('source/edit.html.twig', array(
            'nextSource' => $nextSource,
            'previousSource' => $previousSource,
            'source' => $source,
            'form' => $form->createView(),
            'newTopicForm' => $newTopicForm->createView(),
            'suggestedTopics' => $suggestedTopics
        ));
    }


    /**
     *   @Route("/source/findTopics/{source_id}", name="source_findtopics")
     */
    public function findTopics($source_id, Request $request, TopicService $topicService)
    {
        $repository = $this->getDoctrine()
            ->getRepository(Source::class);
        $source = $repository->find($source_id);
        $suggestedTopics = $topicService->makeTopicSuggestions($source);

        return $this->render('source/findTopics.html.twig', array(
            'source' => $source,
            'suggestedTopics' => $suggestedTopics
        ));
    }

     /**
     *   @Route("/source/parseTranscript/{source_id}", name="source_parsetranscript")
     */
    public function parseTranscript($source_id, Request $request, EntitiesParser $entitiesParser){
        $repository = $this->getDoctrine()
            ->getRepository(Source::class);
        $source = $repository->find($source_id);

        $entitiesParser->initialize($source);

        $foundActors = $entitiesParser->parseActors();
        $foundEntitiesArray = [];

        $i = 0;
        $foundPlaces = $entitiesParser->parsePlaces();

        if($foundPlaces) {
            foreach ($foundPlaces as $foundPlace) {
                $foundEntitiesArray['foundPlaces'][$i]['entity'] = $foundPlace[0];
                $foundEntitiesArray['foundPlaces'][$i]['id'] = $foundPlace[0]->getId();
                $foundEntitiesArray['foundPlaces'][$i]['name'] = $foundPlace[0]->getName();
                $foundEntitiesArray['foundPlaces'][$i]['startPos'] = $foundPlace[1];
                $foundEntitiesArray['foundPlaces'][$i]['endPos'] = $foundPlace[2];
                $i++;
            }
        }
        if($foundActors) {
            foreach ($foundActors as $foundActor) {
                $foundEntitiesArray['foundActors'][$i]['entity'] = $foundActor[0];
                $foundEntitiesArray['foundActors'][$i]['id'] = $foundActor[0]->getId();
                $foundEntitiesArray['foundActors'][$i]['name'] = $foundActor[0]->__toString();
                $foundEntitiesArray['foundActors'][$i]['startPos'] = $foundActor[1];
                $foundEntitiesArray['foundActors'][$i]['endPos'] = $foundActor[2];
                $i++;
            }
        }

        if(array_key_exists('foundActors', $foundEntitiesArray) && array_key_exists('foundPlaces', $foundEntitiesArray)){
            $markingTokens = array_merge($foundEntitiesArray['foundActors'], $foundEntitiesArray['foundPlaces']);
        }
        else if(array_key_exists('foundActors', $foundEntitiesArray)){
            $markingTokens = $foundEntitiesArray['foundActors'];
        }
        else if(array_key_exists('foundPlaces', $foundEntitiesArray)){
            $markingTokens = $foundEntitiesArray['foundPlaces'];
        }
        else{
            $markingTokens = [];
        }


        $taggedText = $entitiesParser->markTokens($markingTokens);

        $foundEntitiesForm = $this->createFormBuilder($foundEntitiesArray)
            ->add('foundActors', CollectionType::class, array(
                'entry_type' => FoundActorType::class,
                'allow_delete' => true,
                'label' => false,
                'by_reference' => false,
            ))
            ->add('foundPlaces', CollectionType::class, array(
                    'entry_type' => FoundPlaceType::class,
                    'allow_delete' => true,
                    'label' => false,
                    'by_reference' => false,
                ))
            ->add('save', SubmitType::class)
            ->getForm();

        $foundEntitiesForm->handleRequest($request);
        if ($foundEntitiesForm->isSubmitted() && $foundEntitiesForm->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $entities = $foundEntitiesForm->getData();
            $authorPlace = null;
            $recipientPlace = null;
            $mentionedPlaces = [];
            $placeRepository = $this->getDoctrine()
                ->getRepository(Place::class);

            foreach($entities['foundPlaces'] as $place){
                if ($place['type'] === 1){
                    $authorPlace = $placeRepository->find($place['id']);
                }
                if ($place['type'] === 2){
                    $recipientPlace = $placeRepository->find($place['id']);
                }
                if ($place['type'] === 3){
                    $mentionedPlaces[] = $placeRepository->find($place['id']);
                }
            }

            foreach($mentionedPlaces as $place){
                $placeMention = new Mention();
                $placeMention->setPlace($place);
                $placeMention->setStartPos($place['startPos']);
                $placeMention->setEndPos($place['endPos']);
                $source->addMention($placeMention);
            }


            $actorRepository = $this->getDoctrine()
                ->getRepository(Actor::class);


            foreach($entities['foundActors'] as $actor){
                echo $actor['name']."[".$actor['id']."]";
                if ($actor['type'] === 1){
                    $author = $actorRepository->find($actor['id']);
                    $action = new Action();
                    $action->setCorrespondent($author->getCorrespondent());
                    $action->setType(1);
                    if(!is_null($authorPlace)){
                        $action->setPlace($authorPlace);
                    }
                    if(!is_null($source->getDate())&&!is_null($source->getDateAccuracy())){
                        $action->setStartDate($source->getRawDate());
                        $action->setStartDateAccuracy($source->getDateAccuracy());
                    }
                    $source->addAction($action);
                }
                if ($actor['type'] === 2){
                    $recipient = $actorRepository->find($actor['id']);
                    $action = new Action();
                    $action->setCorrespondent($recipient->getCorrespondent());
                    $action->setType(2);
                    if(!is_null($recipientPlace)){
                        $action->setPlace($recipientPlace);
                    }
                    $source->addAction($action);
                }
                if ($actor['type'] === 3){
                    $signer = $actorRepository->find($actor['id']);
                    $action = new Action();
                    $action->setCorrespondent($signer->getCorrespondent());
                    $action->setType(3);
                    if(!is_null($source->getDate())&&!is_null($source->getDateAccuracy())){
                        $action->setStartDate($source->getRawDate());
                        $action->setStartDateAccuracy($source->getDateAccuracy());
                    }
                    $source->addAction($action);
                }
                if ($actor['type'] === 4){
                    $mentionedActor = $actorRepository->find($actor['id']);
                    $mention = new Mention();
                    $mention->setActor($mentionedActor);
                    if(!is_null($source->getDate())&&!is_null($source->getDateAccuracy())){
                        $mention->setDate($source->getRawDate());
                        $mention->setDateAccuracy($source->getDateAccuracy());
                    }
                    $source->addMention($mention);
                }
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($source);
            $entityManager->flush();
            return $this->redirectToRoute('source_edit', ['source_id' => $source->getId()]);
        }



        return $this->render('source/parseTranscript.html.twig', array(
            'source' => $source,
            'taggedText' => $taggedText,
            'entitiesForm' => $foundEntitiesForm->createView(),
        ));
    }

    /**
     *   @Route("/source/removefile/{source_id}({file_id}", name="source_remove_file")
     */
    public function removeFile($source_id, $file_id, Request $request, FileUploader $fileUploader){

        $file_repository = $this->getDoctrine()
            ->getRepository(DatabaseFile::class);
        $file = $file_repository->find($file_id);

        $fileUploader->removePdf($file->getSource(), $file);

        return $this->redirect($request->server->get('HTTP_REFERER'));
    }

    /**
     *   @Route("/source/duplicate/{source_id}", name="source_duplicate")
     */

    public function duplicate($source_id, Request $request){
        $repository = $this->getDoctrine()
            ->getRepository(Source::class);
        $source = $repository->find($source_id);
        $new_source = clone $source;
        $new_source->setTitle($new_source->getTitle()." (copy)");
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($new_source);
        $entityManager->flush();
        return $this->redirect($request->server->get('HTTP_REFERER'));
    }

    /**
     *   @Route("/source/delete/{source_id}", name="source_delete")
     */
    public function delete($source_id, Request $request, FileUploader $fileUploader){
        $entityManager = $this->getDoctrine()->getManager();
        $source = $entityManager->getRepository(Source::class)->find($source_id);

        if (!$source) {
            throw $this->createNotFoundException(
                'No source found for id '.$id
            );
        }
        if(!is_null($source->getFile())) {
            $source = $fileUploader->removePdf($source, $source->getFile());
        }


        $entityManager->remove($source);
        $entityManager->flush();

        return $this->redirect($request->server->get('HTTP_REFERER'));
    }

}