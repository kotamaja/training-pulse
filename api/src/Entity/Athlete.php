<?php

namespace App\Entity;

use App\Repository\AthleteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;


#[ORM\Entity(repositoryClass: AthleteRepository::class)]
#[ORM\Table(name: 'athlete')]
#[ORM\UniqueConstraint(name: 'uniq_athlete__public_id', columns: ['public_id'])]
#[ORM\UniqueConstraint(name: 'uniq_athlete__user_id', columns: ['user_id'])]
class Athlete
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 26)]
    private string $publicId;

    #[ORM\OneToOne(inversedBy: 'athlete', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 180)]
    private string $displayName;

    #[ORM\Column(nullable: true)]
    private ?int $birthYear = null;

    #[ORM\Column(nullable: true)]
    private ?float $heightCm = null;

    #[ORM\Column(nullable: true)]
    private ?float $weightKg = null;

    #[ORM\Column(nullable: true)]
    private ?int $restingHeartRate = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxHeartRate = null;

    #[ORM\Column(nullable: true)]
    private ?int $ftpWatts = null;

    #[ORM\OneToMany(mappedBy: 'athlete', targetEntity: AthleteExternalAccount::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $externalAccounts;

    #[ORM\OneToMany(mappedBy: 'athlete', targetEntity: TrainingActivity::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $trainingActivities;


    #[ORM\Column(type: 'utc_datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(User $user, string $displayName)
    {
        $this->publicId = (string) new Ulid();
        $this->user = $user;
        $this->displayName = $displayName;
        $this->externalAccounts = new ArrayCollection();
        $this->trainingActivities = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();

        $user->setAthlete($this);
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }



    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getBirthYear(): ?int
    {
        return $this->birthYear;
    }

    public function setBirthYear(?int $birthYear): void
    {
        $this->birthYear = $birthYear;
    }

    public function getHeightCm(): ?float
    {
        return $this->heightCm;
    }

    public function setHeightCm(?float $heightCm): void
    {
        $this->heightCm = $heightCm;
    }

    public function getWeightKg(): ?float
    {
        return $this->weightKg;
    }

    public function setWeightKg(?float $weightKg): void
    {
        $this->weightKg = $weightKg;
    }

    public function getRestingHeartRate(): ?int
    {
        return $this->restingHeartRate;
    }

    public function setRestingHeartRate(?int $restingHeartRate): void
    {
        $this->restingHeartRate = $restingHeartRate;
    }

    public function getMaxHeartRate(): ?int
    {
        return $this->maxHeartRate;
    }

    public function setMaxHeartRate(?int $maxHeartRate): void
    {
        $this->maxHeartRate = $maxHeartRate;
    }

    public function getFtpWatts(): ?int
    {
        return $this->ftpWatts;
    }

    public function setFtpWatts(?int $ftpWatts): void
    {
        $this->ftpWatts = $ftpWatts;
    }

    public function getExternalAccounts(): Collection
    {
        return $this->externalAccounts;
    }

    public function setExternalAccounts(Collection $externalAccounts): void
    {
        $this->externalAccounts = $externalAccounts;
    }

    public function getTrainingActivities(): Collection
    {
        return $this->trainingActivities;
    }

    public function setTrainingActivities(Collection $trainingActivities): void
    {
        $this->trainingActivities = $trainingActivities;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }




}
