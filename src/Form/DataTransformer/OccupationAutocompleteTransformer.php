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
use App\Entity\Occupation;

class OccupationAutocompleteTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($occupation)
    {
        if (null === $occupation) {
            return '';
        }

        return $occupation->getId();
    }

    public function reverseTransform($occupationId)
    {
        if (!$occupationId) {
            return null;
        }

        $occupation = $this->entityManager
            ->getRepository(Occupation::class)->findOneBy(array('id' => $occupationId));

        if (null === $occupation) {
            throw new TransformationFailedException(sprintf('Occupation "%s" does not exist',
                $occupationId
            ));
        }

        return $occupation;
    }
}