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
use App\Entity\Actor;

class ActorAutocompleteTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($actor)
    {
        if (null === $actor) {
            return '';
        }

        return $actor->getId();
    }

    public function reverseTransform($actorId)
    {
        if (!$actorId) {
            return null;
        }

        $actor = $this->entityManager
            ->getRepository(Actor::class)->findOneBy(array('id' => $actorId));

        if (null === $actor) {
            throw new TransformationFailedException(sprintf('Actor "%s" does not exist',
                $actorId
            ));
        }

        return $actor;
    }
}