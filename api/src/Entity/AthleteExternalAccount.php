<?php

namespace App\Entity;

use App\Enum\ActivitySource;
use App\Enum\ExternalAccountStatus;
use App\Repository\AthleteExternalAccountRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: AthleteExternalAccountRepository::class)]
#[ORM\Table(name: 'athlete_external_account')]
#[ORM\UniqueConstraint(
    name: 'uniq_athlete_external_account__athlete_provider_account',
    columns: ['athlete_id', 'provider', 'provider_account_id']
)]
#[ORM\UniqueConstraint(name: 'uniq_athlete_external_account__public_id', columns: ['public_id'])]
#[ORM\Index(name: 'idx_athlete_external_account__athlete', columns: ['athlete_id'])]
class AthleteExternalAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 26)]
    private string $publicId;

    #[ORM\ManyToOne(targetEntity: Athlete::class, inversedBy: 'externalAccounts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Athlete $athlete;

    #[ORM\Column(enumType: ActivitySource::class)]
    private ActivitySource $provider;

    #[ORM\Column(length: 255)]
    private string $providerAccountId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $accessToken = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: 'utc_datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: 'json')]
    private array $scopes = [];

    #[ORM\Column(enumType: ExternalAccountStatus::class)]
    private ExternalAccountStatus $status = ExternalAccountStatus::Active;

    #[ORM\Column(type: 'utc_datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastSyncAt = null;

    #[ORM\Column(type: 'utc_datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastSuccessfulSyncAt = null;

    #[ORM\Column(type: 'utc_datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastErrorAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $lastErrorMessage = null;


    #[ORM\Column(type: 'utc_datetime_immutable')]
    private DateTimeImmutable $createdAt;


    #[ORM\Column(type: 'utc_datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;


    public function __construct(
        Athlete $athlete,
        ActivitySource $provider,
        string $providerAccountId,
    ) {
        $this->publicId = (string) new Ulid();
        $this->athlete = $athlete;
        $this->provider = $provider;
        $this->providerAccountId = $providerAccountId;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function setPublicId(string $publicId): void
    {
        $this->publicId = $publicId;
    }

    public function getAthlete(): Athlete
    {
        return $this->athlete;
    }

    public function setAthlete(Athlete $athlete): void
    {
        $this->athlete = $athlete;
    }

    public function getProvider(): ActivitySource
    {
        return $this->provider;
    }

    public function setProvider(ActivitySource $provider): void
    {
        $this->provider = $provider;
    }

    public function getProviderAccountId(): string
    {
        return $this->providerAccountId;
    }

    public function setProviderAccountId(string $providerAccountId): void
    {
        $this->providerAccountId = $providerAccountId;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): void
    {
        $this->displayName = $displayName;
        $this->touch();
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
        $this->touch();
    }

    public function requireAccessToken(): string
    {
        if ($this->accessToken === null || $this->accessToken === '') {
            throw new \LogicException('External account has no access token.');
        }

        return $this->accessToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
        $this->touch();
    }

    public function requireRefreshToken(): string
    {
        if ($this->refreshToken === null || $this->refreshToken === '') {
            throw new \LogicException('External account has no refresh token.');
        }

        return $this->refreshToken;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
        $this->touch();
    }

    public function isAccessTokenExpired(DateTimeImmutable $now = new DateTimeImmutable()): bool
    {
        if ($this->expiresAt === null) {
            return true;
        }

        return $this->expiresAt <= $now;
    }

    /**
     * @return list<string>
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @param list<string> $scopes
     */
    public function setScopes(array $scopes): void
    {
        $scopes = array_values(array_unique($scopes));
        sort($scopes);

        $this->scopes = $scopes;
        $this->touch();
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes, true);
    }

    public function getStatus(): ExternalAccountStatus
    {
        return $this->status;
    }

    public function setStatus(ExternalAccountStatus $status): void
    {
        $this->status = $status;
        $this->touch();
    }

    public function getLastSyncAt(): ?DateTimeImmutable
    {
        return $this->lastSyncAt;
    }

    public function markSyncStarted(?DateTimeImmutable $at = null): void
    {
        $this->lastSyncAt = $at ?? new DateTimeImmutable();
        $this->touch();
    }

    public function getLastSuccessfulSyncAt(): ?DateTimeImmutable
    {
        return $this->lastSuccessfulSyncAt;
    }

    public function markSyncSuccessful(?DateTimeImmutable $at = null): void
    {
        $at ??= new DateTimeImmutable();

        $this->lastSyncAt = $at;
        $this->lastSuccessfulSyncAt = $at;
        $this->lastErrorAt = null;
        $this->lastErrorMessage = null;
        $this->status = ExternalAccountStatus::Active;

        $this->touch();
    }

    public function getLastErrorAt(): ?DateTimeImmutable
    {
        return $this->lastErrorAt;
    }

    public function getLastErrorMessage(): ?string
    {
        return $this->lastErrorMessage;
    }

    public function setLastError(string $message, ?DateTimeImmutable $at = null): void
    {
        $this->lastErrorAt = $at ?? new DateTimeImmutable();
        $this->lastErrorMessage = $message;
        $this->status = ExternalAccountStatus::Error;

        $this->touch();
    }

    public function clearLastError(): void
    {
        $this->lastErrorAt = null;
        $this->lastErrorMessage = null;

        if ($this->status === ExternalAccountStatus::Error) {
            $this->status = ExternalAccountStatus::Active;
        }

        $this->touch();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

}
