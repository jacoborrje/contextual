<?php

namespace App\Repository;

use App\Entity\ActorOccupation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method ActorOccupation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActorOccupation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActorOccupation[]    findAll()
 * @method ActorOccupation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActorOccupationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActorOccupation::class);
    }

    // /**
    //  * @return ActorOccupation[] Returns an array of ActorOccupation objects
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
    public function findOneBySomeField($value): ?ActorOccupation
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
