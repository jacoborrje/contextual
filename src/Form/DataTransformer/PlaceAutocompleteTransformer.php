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
use App\Entity\Place;

class PlaceAutocompleteTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($place)
    {
        if (null === $place) {
            return '';
        }

        return $place->getId();
    }

    public function reverseTransform($placeId)
    {
        if (!$placeId) {
            return null;
        }

        $place = $this->entityManager
            ->getRepository(Place::class)->findOneBy(array('id' => $placeId));

        if (null === $place) {
            throw new TransformationFailedException(sprintf('The place "%s" does not exist in the database',
                $placeId
            ));
        }

        return $place;
    }
}