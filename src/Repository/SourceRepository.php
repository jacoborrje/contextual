<?php

namespace App\Repository;

use App\Entity\Source;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Source|null find($id, $lockMode = null, $lockVersion = null)
 * @method Source|null findOneBy(array $criteria, array $orderBy = null)
 * @method Source[]    findAll()
 * @method Source[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SourceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Source::class);
    }

    // /**
    //  * @return Source[] Returns an array of Source objects
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
    public function findOneBySomeField($value): ?Source
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findLastEdited($limit)
    {
        $qb = $this->createQueryBuilder('a')
            ->where("a.updated_at is not NULL")
            ->orderBy('a.updated_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery();

        return $qb->execute();
    }


    public function findAllSourcesWithTopics(){
        $qb = $this->createQueryBuilder('a')
            ->leftJoin("App\Entity\SourceTopic", "t", \Doctrine\ORM\Query\Expr\Join::WITH, "a.id = t.source")
            ->where("t.id is not NULL")
            ->getQuery();

        return $qb->execute();
    }

    public function findAllSourcesWithTopicsAndTranscription(){
        $qb = $this->createQueryBuilder('a')
            ->leftJoin("App\Entity\SourceTopic", "t", \Doctrine\ORM\Query\Expr\Join::WITH, "a.id = t.source")
            ->where("t.id is not NULL AND a.transcription != ''")
            ->getQuery();

        return $qb->execute();
    }

    public function findByAlvinID($alvinID){
        return $this->createQueryBuilder('a')
            ->where("a.alvin_id = :alvinID")
            ->setParameter('alvinID', $alvinID)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findAllSourcesWithTopicsAndTranscriptionAndLanguage($language){
        $qb = $this->createQueryBuilder('a')
            ->leftJoin("App\Entity\SourceTopic", "t", \Doctrine\ORM\Query\Expr\Join::WITH, "a.id = t.source")
            ->where("t.id is not NULL AND a.transcription != '' AND a.language = :language")
            ->setParameter('language', $language)
            ->getQuery();

        return $qb->execute();
    }

    public function findFulltext($query){
        return $this->createQueryBuilder('a')
            ->addSelect("MATCH_AGAINST (a.title, a.excerpt, a.transcription, a.research_notes, :searchterm 'IN NATURAL MODE') as score")
            ->where('MATCH_AGAINST(a.title, a.excerpt, a.transcription, a.research_notes, :searchterm) > 0.8')
            ->setParameter('searchterm', $query)
            ->orderBy('score', 'desc')
            ->getQuery()
            ->getResult();
    }
}