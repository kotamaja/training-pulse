<?php

namespace App\Entity;

use App\Enum\ActivitySource;
use App\Enum\ExternalAccountStatus;
use App\Repository\AthleteExternalAccountRepository;
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

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: 'json')]
    private array $scopes = [];

    #[ORM\Column(enumType: ExternalAccountStatus::class)]
    private ExternalAccountStatus $status = ExternalAccountStatus::Active;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastSyncAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastSuccessfulSyncAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastErrorAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $lastErrorMessage = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;


    public function __construct(
        Athlete $athlete,
        ActivitySource $provider,
        string $providerAccountId,
    ) {
        $this->publicId = (string) new Ulid();
        $this->athlete = $athlete;
        $this->provider = $provider;
        $this->providerAccountId = $providerAccountId;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
