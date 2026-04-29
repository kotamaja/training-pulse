<?php

namespace App\Repository;

use App\Entity\Athlete;
use App\Entity\TrainingActivity;
use App\Enum\ActivitySource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TrainingActivity>
 */
class TrainingActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainingActivity::class);
    }


    public function findOneByExternalIdentityForAthlete(Athlete $athlete, ActivitySource $source, string $externalId): ?TrainingActivity
    {
        return $this->findOneBy([
            'athlete' => $athlete,
            'source' => $source,
            'externalId' => $externalId,
        ]);
    }

    //    /**
    //     * @return TrainingActivity[] Returns an array of TrainingActivity objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TrainingActivity
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
