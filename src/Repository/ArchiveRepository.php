<?php

namespace App\Repository;

use App\Entity\Archive;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Archive|null find($id, $lockMode = null, $lockVersion = null)
 * @method Archive|null findOneBy(array $criteria, array $orderBy = null)
 * @method Archive[]    findAll()
 * @method Archive[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArchiveRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Archive::class);
    }

    public function findAllRootArchives(){
        $qb = $this->createQueryBuilder('a')
            ->where("a.parent is NULL")
            ->andWhere('a.id <> 40')
            ->orderBy('a.name', 'ASC')
            ->getQuery();

        return $qb->execute();
    }

    public function getAllOtherThan($id){
        $qb = $this->createQueryBuilder('a')
            ->where("a.id <> :id")
            ->setParameter('id', $id)
            ->orderBy('a.name', 'ASC')
            ->getQuery();
        return $qb->execute();
    }

    // /**
    //  * @return Archive[] Returns an array of Archive objects
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
    public function findOneBySomeField($value): ?Archive
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
