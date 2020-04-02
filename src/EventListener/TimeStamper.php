<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-03-11
 * Time: 09:07
 * src/EventListener/TimeStamper.php
 */

namespace App\EventListener;



use Doctrine\ORM\Event\OnFlushEventArgs;
use App\Entity\Source;
use App\Entity\Series;
use App\Entity\Volume;
use App\Entity\Actor;
use App\Entity\Place;
use App\Entity\Archive;
use App\Entity\Institution;


use \DateTime;

class TimeStamper
{

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $entities = array_merge($uow->getScheduledEntityInsertions(), $uow->getScheduledEntityUpdates());
        foreach ($entities as $entity) {
            if ($entity instanceof Source) {
                $entity->setUpdatedAt(null);
                $meta = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($meta, $entity);

                $volume = $entity->getVolume();
                $volume->setUpdatedAt(null);
                $meta = $em->getClassMetadata(get_class($volume));
                $uow->recomputeSingleEntityChangeSet($meta, $volume);


                $series = $volume->getSeries();
                while(!is_null($series)){
                    $series->setUpdatedAt(null);
                    $meta = $em->getClassMetadata(get_class($series));
                    $uow->recomputeSingleEntityChangeSet($meta, $series);

                    $archive = $series->getArchive();
                    if(!is_null($archive)){
                        $archive->setUpdatedAt(null);
                        $meta = $em->getClassMetadata(get_class($archive));
                        $uow->recomputeSingleEntityChangeSet($meta, $archive);
                    }
                    $series = $series->getParent();
                }

            }
            else if ($entity instanceof Volume) {
                echo "Entity is a volume!";
                $entity->setUpdatedAt(null);
                $series = $entity->getSeries();
                while(!is_null($series)){
                    $series->setUpdatedAt(null);
                    $meta = $em->getClassMetadata(get_class($series));
                    $uow->recomputeSingleEntityChangeSet($meta, $series);
                    $archive = $series->getArchive();
                    if(!is_null($archive)){
                        $archive->setUpdatedAt(null);
                        $meta = $em->getClassMetadata(get_class($archive));
                        $uow->recomputeSingleEntityChangeSet($meta, $archive);
                    }
                    $series = $series->getParent();
                }
                $meta = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($meta, $entity);
            }
            else if ($entity instanceof Series) {
                $entity->setUpdatedAt(null);
                $series = $entity->getParent();
                while(!is_null($series)){
                    $series->setUpdatedAt();
                    $meta = $em->getClassMetadata(get_class($series));
                    $uow->recomputeSingleEntityChangeSet($meta, $series);
                    $archive = $series->getArchive();
                    if(!is_null($archive)){
                        $archive->setUpdatedAt();
                        $meta = $em->getClassMetadata(get_class($archive));
                        $uow->recomputeSingleEntityChangeSet($meta, $archive);
                    }
                    $series = $series->getParent();
                }
                $archive = $entity->getArchive();
                if(!is_null($archive)) {
                    $archive->setUpdatedAt();
                    $meta = $em->getClassMetadata(get_class($archive));
                    $uow->recomputeSingleEntityChangeSet($meta, $archive);
                }
                $uow->persist($entity);
            }

            else if($entity instanceof Actor || $entity instanceof Place || $entity instanceof Archive|| $entity instanceof Institution) {
                $entity->setUpdatedAt(null);
                $meta = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($meta, $entity);
            }
        }
    }
}