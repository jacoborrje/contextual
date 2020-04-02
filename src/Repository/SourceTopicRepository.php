<?php

namespace App\Repository;

use App\Entity\SourceTopic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SourceTopic|null find($id, $lockMode = null, $lockVersion = null)
 * @method SourceTopic|null findOneBy(array $criteria, array $orderBy = null)
 * @method SourceTopic[]    findAll()
 * @method SourceTopic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SourceTopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SourceTopic::class);
    }

    // /**
    //  * @return SourceTopic[] Returns an array of SourceTopic objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SourceTopic
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
