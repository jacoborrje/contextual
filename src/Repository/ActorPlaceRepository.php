<?php

namespace App\Repository;

use App\Entity\ActorPlace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ActorPlace|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActorPlace|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActorPlace[]    findAll()
 * @method ActorPlace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActorPlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActorPlace::class);
    }

    // /**
    //  * @return ActorPlace[] Returns an array of ActorPlace objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ActorPlace
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
