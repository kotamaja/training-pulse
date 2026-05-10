<?php

namespace App\Entity;

use App\Repository\RefreshTokenRepository;
use App\Security\RefreshToken\RefreshTokenMode;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_token')]
#[ORM\UniqueConstraint(name: 'uniq_refresh_token__token_hash', columns: ['token_hash'])]
#[ORM\Index(name: 'idx_refresh_token__user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_refresh_token__expires_at', columns: ['expires_at'])]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 64)]
    private string $tokenHash;

    #[ORM\Column(enumType: RefreshTokenMode::class)]
    private RefreshTokenMode $mode;

    #[ORM\Column(type: 'utc_datetime_immutable')]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(type: 'utc_datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'utc_datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastUsedAt = null;

    #[ORM\Column(type: 'utc_datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $revokedAt = null;

    public function __construct(User              $user,
                                string            $tokenHash,
                                RefreshTokenMode  $mode,
                                DateTimeImmutable $expiresAt)
    {
        $this->user = $user;
        $this->tokenHash = $tokenHash;
        $this->mode = $mode;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function getMode(): RefreshTokenMode
    {
        return $this->mode;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastUsedAt(): ?DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function getRevokedAt(): ?DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function isExpired(DateTimeImmutable $now = new DateTimeImmutable()): bool
    {
        return $this->expiresAt <= $now;
    }

    public function isRevoked(): bool
    {
        return $this->revokedAt !== null;
    }

    public function isUsable(DateTimeImmutable $now = new DateTimeImmutable()): bool
    {
        return !$this->isRevoked() && !$this->isExpired($now);
    }

    public function markUsed(): void
    {
        $this->lastUsedAt = new DateTimeImmutable();
    }

    public function revoke(): void
    {
        if ($this->revokedAt !== null) {
            return;
        }

        $this->revokedAt = new DateTimeImmutable();
    }
}
