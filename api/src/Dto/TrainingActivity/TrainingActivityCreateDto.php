<?php

namespace App\Dto\TrainingActivity;

use App\Enum\ActivitySource;
use App\Enum\SportType;
use Symfony\Component\Validator\Constraints as Assert;

final class TrainingActivityCreateDto
{
    #[Assert\NotNull]
    public ActivitySource $source;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $externalId;

    #[Assert\NotBlank]
    #[Assert\Length(max: 512)]
    public string $name;

    #[Assert\NotNull]
    public SportType $sportType;

    #[Assert\NotNull]
    public \DateTimeImmutable $startedAt;

    public ?\DateTimeImmutable $startedAtLocal = null;

    #[Assert\Length(max: 100)]
    public ?string $timezone = null;

    #[Assert\PositiveOrZero]
    public ?float $distanceM = null;

    #[Assert\PositiveOrZero]
    public ?int $movingTimeS = null;

    #[Assert\PositiveOrZero]
    public ?int $elapsedTimeS = null;

    #[Assert\PositiveOrZero]
    public ?float $elevationGainM = null;

    #[Assert\PositiveOrZero]
    public ?float $averageSpeedMps = null;

    #[Assert\PositiveOrZero]
    public ?float $maxSpeedMps = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(250)]
    public ?float $averageHeartrate = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(250)]
    public ?float $maxHeartrate = null;

    #[Assert\PositiveOrZero]
    public ?float $averageWatts = null;

    #[Assert\PositiveOrZero]
    public ?float $maxWatts = null;

    #[Assert\PositiveOrZero]
    public ?float $calories = null;

    public ?string $summaryPolyline = null;

    /**
     * Coordinates in GeoJSON order: [longitude, latitude].
     *
     * @var list<array{0: float, 1: float}>|null
     */
    public ?array $routeCoordinates = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $rawExternalSummary = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $rawExternalDetail = null;

    public ?\DateTimeImmutable $syncedAt = null;

    public ?\DateTimeImmutable $streamsSyncedAt = null;
}
