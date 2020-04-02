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
use App\Entity\Topic;
use App\Form\TopicType;
use Symfony\Component\HttpFoundation\JsonResponse;

class TopicController extends AbstractController
{
    /**
    *   @Route("/topics/index", name="topic_index")
    */
    public function index(Request $request){

        $repository = $this->getDoctrine()
            ->getRepository(Topic::class);

        $all_topics = $repository->findAll();



        $form = $this->createForm(TopicType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('topic_index');
        }

        return $this->render('topic/index.html.twig', [
            'all_topics' => $all_topics,
            'form' => $form->createView()

        ]);
    }


    /**
     *   @Route("/topic/view/{topic_id}", name="topic_view")
     */
    public function view($topic_id){
        $repository = $this->getDoctrine()
            ->getRepository(Topic::class);
        $topic = $repository->find($topic_id);

        return $this->render('topic/view.html.twig', [
            'topic' => $topic,
        ]);
    }

    /**
     *   @Route("/topic/delete/{topic_id}", name="topic_delete")
     */
    public function delete($topic_id){
        $entityManager = $this->getDoctrine()->getManager();
        $topic = $entityManager->getRepository(Topic::class)->find($topic_id);

        if (!$topic) {
            throw $this->createNotFoundException(
                'No topic found for id '.$id
            );
        }

        $entityManager->remove($topic);
        $entityManager->flush();

        return $this->redirectToRoute('topic_index');
    }

    /**
     * @Route("/autocomplete/topic", name="topic_autocomplete")
     */
    public function autocompleteAction(Request $request)
    {
        $names = array();
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(Topic::class)->createQueryBuilder('c')
            ->where('c.topic LIKE :topic')
            ->setParameter('topic', '%'.$term.'%')
            ->getQuery()
            ->getResult();

        foreach ($entities as $entity)
        {
            $names[] = ['id' => $entity->getId(), 'topic' => $entity->getTopic()];
        }

        $response = new JsonResponse();
        $response->setData($names);

        return $response;
    }
}