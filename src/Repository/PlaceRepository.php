<?php

namespace App\Repository;

use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Place|null find($id, $lockMode = null, $lockVersion = null)
 * @method Place|null findOneBy(array $criteria, array $orderBy = null)
 * @method Place[]    findAll()
 * @method Place[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    public function findAllRootPlaces(){
        $qb = $this->createQueryBuilder('p')
            ->where("p.parent is NULL")
            ->orderBy('p.name', 'ASC')
            ->getQuery();

        return $qb->execute();
    }

    public function findChildren($place_id){
        $qb = $this->createQueryBuilder('p')
            ->where("p.parent = :id")
            ->setParameter('id', $place_id)
            ->orderBy('p.name', 'ASC')
            ->getQuery();

        return $qb->execute();
    }

    public function findOneByNameAndAltNames($fields): ?Place
    {
        return $this->createQueryBuilder('a')
            ->where('a.name = :name')
            ->orWhere("a.alt_names LIKE :alt_name")
            ->setParameter('name', $fields['name'])
            ->setParameter('alt_name', '%'.$fields['name'].'%')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByCountriesAndName($fields): ?Place
    {
        return $this->createQueryBuilder('a')
            ->where('a.name = :name')
            ->orWhere("a.alt_names LIKE :alt_name")
            ->andWhere("a.type = 1")
            ->setParameter('name', $fields['name'])
            ->setParameter('alt_name', '%'.$fields['name'].'%')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByNameAndAltNamesAndLonLat($fields): ?Place
    {
        return $this->createQueryBuilder('a')
            ->where('a.name = :name OR a.alt_names LIKE :alt_name')
            ->andWhere("a.lat > :lat_min")
            ->andWhere("a.lat < :lat_max")
            ->andWhere("a.lng > :lng_min")
            ->andWhere("a.lng < :lng_max")
            ->setParameter('name', $fields['name'])
            ->setParameter('alt_name', '%'.$fields['name'].'%')
            ->setParameter('lat_min', $fields['lat']-0.5)
            ->setParameter('lat_max', $fields['lat']+0.5)
            ->setParameter('lng_min', $fields['lng']-0.5)
            ->setParameter('lng_max', $fields['lng']+0.5)
            ->getQuery()
            ->getOneOrNullResult();
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
    //  * @return Place[] Returns an array of Place objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Place
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
