<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\QueryParameter;
use App\Dto\TrainingActivity\TrainingActivityDetailDto;
use App\Dto\TrainingActivity\TrainingActivityRouteGeoJsonDto;
use App\Dto\TrainingActivity\TrainingActivitySummaryDto;
use App\Enum\ActivitySource;
use App\Enum\SportType;
use App\Repository\TrainingActivityRepository;
use App\Security\Role;
use App\State\CollectionProvider;
use App\State\ItemProvider;
use Doctrine\ORM\Mapping as ORM;
use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;


#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: [
                'skip_null_values' => false,
            ],
            security: "is_granted('" . Role::ROLE_USER . "')",
            output: TrainingActivitySummaryDto::class,
            provider: CollectionProvider::class,
            parameters: [
                'id' => new QueryParameter(
                    schema: [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'uniqueItems' => true,
                    ],
                    filter: new ExactFilter(),
                    property: 'publicId',
                    constraints: [
                        new Assert\All([
                            new Assert\NotBlank(),
                            new Assert\Ulid(),
                        ]),
                    ],
                    castToArray: true,
                ),
                'sportType' => new QueryParameter(
                    schema: ['type' => 'string'],
                    filter: new ExactFilter(),
                    property: 'sportType',
                ),

            ]
        ),
        new Get(
            uriVariables: [
                'id' => new Link(fromClass: TrainingActivity::class, identifiers: ['publicId']),
            ],
            normalizationContext: [
                'skip_null_values' => false,
            ],
            security: "is_granted('" . Role::ROLE_USER . "')",
            output: TrainingActivityDetailDto::class,
            provider: ItemProvider::class,
        ),
        new Get(
            uriTemplate: '/training_activities/{id}/route',
            uriVariables: [
                'id' => new Link(fromClass: TrainingActivity::class, identifiers: ['publicId']),
            ],
            security: "is_granted('" . Role::ROLE_USER . "')",
            output: TrainingActivityRouteGeoJsonDto::class,
            provider: ItemProvider::class
        ),
    ],
    routePrefix: '/v1',
)]
#[ORM\Entity(repositoryClass: TrainingActivityRepository::class)]
#[ORM\Table(name: 'training_activity')]
#[ORM\UniqueConstraint(name: 'uniq_training_activity__public_id', columns: ['public_id'])]
#[ORM\UniqueConstraint(
    name: 'uniq_training_activity__athlete_source_external_id',
    columns: ['athlete_id', 'source', 'external_id']
)]
#[ORM\Index(name: 'idx_training_activity__athlete_started_at', columns: ['athlete_id', 'started_at'])]
#[ORM\Index(name: 'idx_training_activity__sport_type', columns: ['sport_type'])]
#[ORM\Index(name: 'idx_training_activity__athlete', columns: ['athlete_id'])]
class TrainingActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 26)]
    private string $publicId;

    #[ORM\ManyToOne(targetEntity: Athlete::class, inversedBy: 'trainingActivities')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Athlete $athlete;

    #[ORM\Column(enumType: ActivitySource::class)]
    private ActivitySource $source;

    #[ORM\Column(length: 255)]
    private string $externalId;

    #[ORM\Column(length: 512)]
    private string $name;


    #[ORM\Column(enumType: SportType::class)]
    private SportType $sportType;

    #[ORM\Column]
    private \DateTimeImmutable $startedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAtLocal = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $timezone = null;

    #[ORM\Column(nullable: true)]
    private ?float $distanceM = null;

    #[ORM\Column(nullable: true)]
    private ?int $movingTimeS = null;

    #[ORM\Column(nullable: true)]
    private ?int $elapsedTimeS = null;

    #[ORM\Column(nullable: true)]
    private ?float $elevationGainM = null;

    #[ORM\Column(nullable: true)]
    private ?float $averageSpeedMps = null;

    #[ORM\Column(nullable: true)]
    private ?float $maxSpeedMps = null;

    #[ORM\Column(nullable: true)]
    private ?float $averageHeartrate = null;

    #[ORM\Column(nullable: true)]
    private ?float $maxHeartrate = null;

    #[ORM\Column(nullable: true)]
    private ?float $averageWatts = null;

    #[ORM\Column(nullable: true)]
    private ?float $maxWatts = null;

    #[ORM\Column(nullable: true)]
    private ?float $calories = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $summaryPolyline = null;

    #[ORM\Column(type: 'linestring', nullable: true)]
    private ?LineString $route = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $rawExternalSummary = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $rawExternalDetail = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $syncedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(
        Athlete            $athlete,
        ActivitySource     $source,
        string             $externalId,
        string             $name,
        SportType          $sportType,
        \DateTimeImmutable $startedAt,
    )
    {
        $this->publicId = (string)new Ulid();
        $this->athlete = $athlete;
        $this->source = $source;
        $this->externalId = $externalId;
        $this->name = $name;
        $this->sportType = $sportType;
        $this->startedAt = $startedAt;
        $this->createdAt = new \DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getAthlete(): Athlete
    {
        return $this->athlete;
    }

    public function getSource(): ActivitySource
    {
        return $this->source;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->touch();
    }

    public function getSportType(): SportType
    {
        return $this->sportType;
    }

    public function setSportType(SportType $sportType): void
    {
        $this->sportType = $sportType;
        $this->touch();
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): void
    {
        $this->startedAt = $startedAt;
        $this->touch();
    }

    public function getStartedAtLocal(): ?\DateTimeImmutable
    {
        return $this->startedAtLocal;
    }

    public function setStartedAtLocal(?\DateTimeImmutable $startedAtLocal): void
    {
        $this->startedAtLocal = $startedAtLocal;
        $this->touch();
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): void
    {
        $this->timezone = $timezone;
        $this->touch();
    }

    public function getDistanceM(): ?float
    {
        return $this->distanceM;
    }

    public function setDistanceM(?float $distanceM): void
    {
        $this->distanceM = $distanceM;
        $this->touch();
    }

    public function getMovingTimeS(): ?int
    {
        return $this->movingTimeS;
    }

    public function setMovingTimeS(?int $movingTimeS): void
    {
        $this->movingTimeS = $movingTimeS;
        $this->touch();
    }

    public function getElapsedTimeS(): ?int
    {
        return $this->elapsedTimeS;
    }

    public function setElapsedTimeS(?int $elapsedTimeS): void
    {
        $this->elapsedTimeS = $elapsedTimeS;
        $this->touch();
    }

    public function getElevationGainM(): ?float
    {
        return $this->elevationGainM;
    }

    public function setElevationGainM(?float $elevationGainM): void
    {
        $this->elevationGainM = $elevationGainM;
        $this->touch();
    }

    public function getAverageSpeedMps(): ?float
    {
        return $this->averageSpeedMps;
    }

    public function setAverageSpeedMps(?float $averageSpeedMps): void
    {
        $this->averageSpeedMps = $averageSpeedMps;
        $this->touch();
    }

    public function getMaxSpeedMps(): ?float
    {
        return $this->maxSpeedMps;
    }

    public function setMaxSpeedMps(?float $maxSpeedMps): void
    {
        $this->maxSpeedMps = $maxSpeedMps;
        $this->touch();
    }

    public function getAverageHeartrate(): ?float
    {
        return $this->averageHeartrate;
    }

    public function setAverageHeartrate(?float $averageHeartrate): void
    {
        $this->averageHeartrate = $averageHeartrate;
        $this->touch();
    }

    public function getMaxHeartrate(): ?float
    {
        return $this->maxHeartrate;
    }

    public function setMaxHeartrate(?float $maxHeartrate): void
    {
        $this->maxHeartrate = $maxHeartrate;
        $this->touch();
    }

    public function getAverageWatts(): ?float
    {
        return $this->averageWatts;
    }

    public function setAverageWatts(?float $averageWatts): void
    {
        $this->averageWatts = $averageWatts;
        $this->touch();
    }

    public function getMaxWatts(): ?float
    {
        return $this->maxWatts;
    }

    public function setMaxWatts(?float $maxWatts): void
    {
        $this->maxWatts = $maxWatts;
        $this->touch();
    }

    public function getCalories(): ?float
    {
        return $this->calories;
    }

    public function setCalories(?float $calories): void
    {
        $this->calories = $calories;
        $this->touch();
    }

    public function getSummaryPolyline(): ?string
    {
        return $this->summaryPolyline;
    }

    public function setSummaryPolyline(?string $summaryPolyline): void
    {
        $this->summaryPolyline = $summaryPolyline;
        $this->touch();
    }

    public function getRoute(): ?LineString
    {
        return $this->route;
    }

    public function setRoute(?LineString $route): void
    {
        $this->route = $route;
        $this->touch();
    }

    public function hasRoute(): bool
    {
        return $this->route !== null;
    }

    public function getRawExternalSummary(): ?array
    {
        return $this->rawExternalSummary;
    }

    public function setRawExternalSummary(?array $rawExternalSummary): void
    {
        $this->rawExternalSummary = $rawExternalSummary;
        $this->touch();
    }

    public function getRawExternalDetail(): ?array
    {
        return $this->rawExternalDetail;
    }

    public function setRawExternalDetail(?array $rawExternalDetail): void
    {
        $this->rawExternalDetail = $rawExternalDetail;
        $this->touch();
    }

    public function getSyncedAt(): ?\DateTimeImmutable
    {
        return $this->syncedAt;
    }

    public function setSyncedAt(?\DateTimeImmutable $syncedAt): void
    {
        $this->syncedAt = $syncedAt;
        $this->touch();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
