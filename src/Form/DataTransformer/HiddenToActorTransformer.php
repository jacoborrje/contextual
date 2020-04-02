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

class HiddenToActorTransformer implements DataTransformerInterface
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
        echo $actor->getId()."<br>";
        return $actor->getId();
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $actor = $this->entityManager
            ->getRepository(Actor::class)->findOneBy(array('id' => $id));

        if (null === $actor) {
            throw new TransformationFailedException(sprintf('Actor with id "%s" does not exist',
                $id
            ));
        }

        return $actor;
    }
}