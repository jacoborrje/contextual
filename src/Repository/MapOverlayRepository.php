<?php

namespace App\Repository;

use App\Entity\MapOverlay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MapOverlay|null find($id, $lockMode = null, $lockVersion = null)
 * @method MapOverlay|null findOneBy(array $criteria, array $orderBy = null)
 * @method MapOverlay[]    findAll()
 * @method MapOverlay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MapOverlayRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MapOverlay::class);
    }

    // /**
    //  * @return MapOverlay[] Returns an array of MapOverlay objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MapOverlay
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
