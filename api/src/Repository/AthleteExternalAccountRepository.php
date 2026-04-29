<?php

namespace App\Repository;

use App\Entity\Athlete;
use App\Entity\AthleteExternalAccount;
use App\Enum\ActivitySource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AthleteExternalAccount>
 */
class AthleteExternalAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AthleteExternalAccount::class);
    }


    public function findOneForAthleteAndProvider(Athlete $athlete, ActivitySource $provider): ?AthleteExternalAccount
    {
        return $this->findOneBy([
            'athlete' => $athlete,
            'provider' => $provider,
        ]);
    }

    //    /**
    //     * @return AtheleteExternalAccount[] Returns an array of AtheleteExternalAccount objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?AtheleteExternalAccount
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
