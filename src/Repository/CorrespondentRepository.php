<?php

namespace App\Repository;

use App\Entity\Correspondent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Correspondent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Correspondent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Correspondent[]    findAll()
 * @method Correspondent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorrespondentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Correspondent::class);
    }


    public function findOneByNames($fields): ?Actor
    {
        $qb = $this->createQueryBuilder();
        $qb->select('c');
    }


    public function findByTerm($term){
        return $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('App:Actor', 'a', 'WITH', 'a.id = c.actor')
            ->leftJoin('App:Institution', 'i', 'WITH', 'i.id = c.institution')
            ->orderBy('c.id', 'DESC')
            ->andWhere('a.first_name LIKE :name OR a.surname LIKE :name OR YEAR(a.birthdate) LIKE :name OR YEAR(a.date_of_death) LIKE :name')
            ->orWhere('i.name LIKE :name')
            ->setParameter('name', '%'.$term.'%')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Correspondent[] Returns an array of Correspondent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Correspondent
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
