<?php

namespace App\Repository;

use App\Entity\Actor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Actor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Actor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Actor[]    findAll()
 * @method Actor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Actor::class);
    }

    public function findAll()
    {
        return $this->findBy(array(), array('surname' => 'ASC'));
    }


    public function findOneBySurnameAndAltFirstNames($fields): ?Actor
    {
        return $this->createQueryBuilder('a')
            ->where('a.surname = :surname')
            ->andWhere("a.alt_first_names LIKE :first_name")
            ->setParameter('surname', $fields['surname'])
            ->setParameter('first_name', '%'.$fields['first_name'].'%')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByAltSurnamesAndAltFirstNames($fields): ?Actor
    {
        return $this->createQueryBuilder('a')
            ->where('a.surname = :surname OR a.alt_surnames LIKE :alt_surname')
            ->andWhere("a.first_name = :first_name OR a.alt_first_names LIKE :alt_first_name")
            ->setParameter('surname', $fields['surname'])
            ->setParameter('alt_surname', '%'.$fields['surname'].'%')
            ->setParameter('first_name', $fields['first_name'])
            ->setParameter('alt_first_name', '%'.$fields['first_name'].'%')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAnyByAltSurnamesAndAltFirstNames($fields): ?Array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.surname = :surname OR a.alt_surnames LIKE :alt_surname')
            ->andWhere("a.first_name = :first_name OR a.alt_first_names LIKE :alt_first_name")
            ->setParameter('surname', $fields['surname'])
            ->setParameter('alt_surname', '%'.$fields['surname'].'%')
            ->setParameter('first_name', $fields['first_name'])
            ->setParameter('alt_first_name', '%'.$fields['first_name'].'%')
            ->getQuery();
        return $qb->execute();
    }

    public function findOneByNamesAndDates($fields){
        return $this->createQueryBuilder('a')
            ->where('a.surname = :surname OR a.alt_surnames LIKE :alt_surname')
            ->andWhere("a.first_name = :first_name OR a.alt_first_names LIKE :alt_first_name")
            ->andWhere("YEAR(a.birthdate) = YEAR(:birthdate)")
            ->andWhere("YEAR(a.date_of_death) = YEAR(:date_of_death)")
            ->setParameter('surname', $fields['surname'])
            ->setParameter('alt_surname', '%'.$fields['surname'].'%')
            ->setParameter('first_name', $fields['first_name'])
            ->setParameter('alt_first_name', '%'.$fields['first_name'].'%')
            ->setParameter('birthdate', $fields['birthdate'])
            ->setParameter('date_of_death', $fields['date_of_death'])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAnyByNamesAndDates($fields){
        return $this->createQueryBuilder('a')
            ->where('a.surname = :surname OR a.alt_surnames LIKE :alt_surname')
            ->andWhere("a.first_name = :first_name OR a.alt_first_names LIKE :alt_first_name")
            ->andWhere("YEAR(a.birthdate) = YEAR(:birthdate)")
            ->andWhere("YEAR(a.date_of_death) = YEAR(:date_of_death)")
            ->setParameter('surname', $fields['surname'])
            ->setParameter('alt_surname', '%'.$fields['surname'].'%')
            ->setParameter('first_name', $fields['first_name'])
            ->setParameter('alt_first_name', '%'.$fields['first_name'].'%')
            ->setParameter('birthdate', $fields['birthdate'])
            ->setParameter('date_of_death', $fields['date_of_death'])
            ->getQuery()
            ->execute();
    }




    public function findLastEdited($limit)
    {
        $qb = $this->createQueryBuilder('a')
            ->where("a.updated_at is not NULL")
            ->orderBy('a.updated_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery();

        return $qb->execute();
    }

    // /**
    //  * @return Actor[] Returns an array of Actor objects
    //  */
    /*
    public function findOneBySomeField($value): ?Actor
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
