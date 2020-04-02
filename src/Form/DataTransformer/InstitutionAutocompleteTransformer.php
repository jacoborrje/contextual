<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-02-21
 * Time: 18:48
 */
namespace App\Form\DataTransformer;

use App\Entity\Institution;
use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use App\Entity\Actor;

class InstitutionAutocompleteTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($institution)
    {
        if (null === $institution) {
            return '';
        }

        return $institution->getId();
    }

    public function reverseTransform($institutionId)
    {
        if (!$institutionId) {
            return null;
        }

        $institution = $this->entityManager
            ->getRepository(Institution::class)->findOneBy(array('id' => $institutionId));

        if (null === $institution) {
            throw new TransformationFailedException(sprintf('Actor "%s" does not exist',
                $institutionId
            ));
        }

        return $institution;
    }
}