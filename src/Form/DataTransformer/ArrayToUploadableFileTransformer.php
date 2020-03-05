<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-02-21
 * Time: 18:48
 */
namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Exception\TransformationFailedException;
use App\Entity\Topic;

class HiddenToTopicTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($topic)
    {
        if (null === $topic) {
            return '';
        }
        return $topic->getId();
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $topic = $this->entityManager
            ->getRepository(Topic::class)->findOneBy(array('id' => $id));

        if (null === $topic) {
            throw new TransformationFailedException(sprintf('Topic with id "%s" does not exist',
                $id
            ));
        }

        return $topic;
    }
}