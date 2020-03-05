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
use App\Entity\Actor;

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

        echo "reverseTransform!<br>";

        return $actor->getSurname().", ".$actor->getFirstName();
    }

    public function reverseTransform($actorName)
    {
        if (!$actorName) {
            return null;
        }

        $actorNames = explode (", ", $actorName);
        $first_name = $actorNames[1];
        $surname = $actorNames[0];

        $actor = $this->entityManager
            ->getRepository(Actor::class)->findOneBy(array('first_name' => $first_name, 'surname' => $surname));

        if (null === $actor) {
            throw new TransformationFailedException(sprintf('Actor "%s" does not exist',
                $actorName
            ));
        }
        echo "transform!<br>";


        return $actor;
    }
}