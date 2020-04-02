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
use App\Entity\Correspondent;

class CorrespondentAutocompleteTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($correspondent)
    {
        if (null === $correspondent) {
            return '';
        }

        return $correspondent->getId();
    }

    public function reverseTransform($correspondentId)
    {
        $correspondent = $this->entityManager
            ->getRepository(Correspondent::class)->findOneBy(array('id' => $correspondentId));

        if (null === $correspondent) {
            throw new TransformationFailedException(sprintf('Correspondent "%s" does not exist',
                $correspondentId
            ));
        }

        return $correspondent;
    }
}