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

class ActorAutocompleteTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($actor)
    {
        if (null === $actor) {
            return '';
        }

        return $actor->getName();
    }

    public function reverseTransform($actorName)
    {
        if (!$actorName) {
            return null;
        }

        $actor = $this->entityManager
            ->getRepository('Actor::class')->findOneBy(array('name' => $actorName));

        if (null === $actor) {
            throw new TransformationFailedException(sprintf('"%s" does not exist',
                $actorName
            ));
        }

        return $actor;
    }
}