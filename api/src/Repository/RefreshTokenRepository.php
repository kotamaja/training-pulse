<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RefreshToken>
 */
class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findOneByTokenHash(string $tokenHash): ?RefreshToken
    {
        return $this->findOneBy([
            'tokenHash' => $tokenHash,
        ]);
    }

    public function purgeExpiredOrRevokedBefore(DateTimeImmutable $threshold): int
    {
        return $this->createQueryBuilder('rt')
            ->delete()
            ->where('rt.expiresAt < :threshold')
            ->orWhere('rt.revokedAt IS NOT NULL AND rt.revokedAt < :threshold')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->execute();
    }
}
