<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-02-21
 * Time: 18:48
 */
namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use App\Entity\Topic;

class TopicAutocompleteTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($topic)
    {
        if (null === $topic) {
            return '';
        }

        return $topic->getTopic();
    }

    public function reverseTransform($topicName)
    {
        if (!$topicName) {
            return null;
        }

        $topic = $this->entityManager
            ->getRepository(Topic::class)->findOneBy(array('topic' => $topicName));

        if (null === $topic) {
            throw new TransformationFailedException(sprintf('The topic "%s" does not exist in the database',
                $topicName
            ));
        }

        return $topic;
    }
}