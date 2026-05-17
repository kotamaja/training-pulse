<?php

namespace App\Dto\TrainingActivity;

use App\Enum\SportType;
use Symfony\Component\Validator\Constraints as Assert;

final class TrainingActivityUpdateDto
{
    private bool $nameProvided = false;
    private bool $sportTypeProvided = false;
    private bool $startedAtProvided = false;
    private bool $startedAtLocalProvided = false;
    private bool $timezoneProvided = false;
    private bool $distanceMProvided = false;
    private bool $movingTimeSProvided = false;
    private bool $elapsedTimeSProvided = false;
    private bool $elevationGainMProvided = false;
    private bool $averageSpeedMpsProvided = false;
    private bool $maxSpeedMpsProvided = false;
    private bool $averageHeartrateProvided = false;
    private bool $maxHeartrateProvided = false;
    private bool $averageWattsProvided = false;
    private bool $maxWattsProvided = false;
    private bool $caloriesProvided = false;
    private bool $summaryPolylineProvided = false;
    private bool $rawExternalSummaryProvided = false;
    private bool $rawExternalDetailProvided = false;
    private bool $syncedAtProvided = false;

    private bool $routeCoordinatesProvided = false;

    private bool $streamsSyncedAtProvided = false;

    #[Assert\Length(max: 512)]
    private ?string $name = null;

    private ?SportType $sportType = null;

    private ?\DateTimeImmutable $startedAt = null;

    private ?\DateTimeImmutable $startedAtLocal = null;

    #[Assert\Length(max: 100)]
    private ?string $timezone = null;

    #[Assert\PositiveOrZero]
    private ?float $distanceM = null;

    #[Assert\PositiveOrZero]
    private ?int $movingTimeS = null;

    #[Assert\PositiveOrZero]
    private ?int $elapsedTimeS = null;

    #[Assert\PositiveOrZero]
    private ?float $elevationGainM = null;

    #[Assert\PositiveOrZero]
    private ?float $averageSpeedMps = null;

    #[Assert\PositiveOrZero]
    private ?float $maxSpeedMps = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(250)]
    private ?float $averageHeartrate = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(250)]
    private ?float $maxHeartrate = null;

    #[Assert\PositiveOrZero]
    private ?float $averageWatts = null;

    #[Assert\PositiveOrZero]
    private ?float $maxWatts = null;

    #[Assert\PositiveOrZero]
    private ?float $calories = null;

    private ?string $summaryPolyline = null;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $rawExternalSummary = null;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $rawExternalDetail = null;

    private ?\DateTimeImmutable $syncedAt = null;

    /**
     * Coordinates in GeoJSON order: [longitude, latitude].
     *
     * @var list<array{0: float, 1: float}>|null
     */
    private ?array $routeCoordinates = null;

    private ?\DateTimeImmutable $streamsSyncedAt = null;

    public function setName(?string $name): void
    {
        $this->nameProvided = true;
        $this->name = $name;
    }

    public function isNameProvided(): bool
    {
        return $this->nameProvided;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setSportType(?SportType $sportType): void
    {
        $this->sportTypeProvided = true;
        $this->sportType = $sportType;
    }

    public function isSportTypeProvided(): bool
    {
        return $this->sportTypeProvided;
    }

    public function getSportType(): ?SportType
    {
        return $this->sportType;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): void
    {
        $this->startedAtProvided = true;
        $this->startedAt = $startedAt;
    }

    public function isStartedAtProvided(): bool
    {
        return $this->startedAtProvided;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAtLocal(?\DateTimeImmutable $startedAtLocal): void
    {
        $this->startedAtLocalProvided = true;
        $this->startedAtLocal = $startedAtLocal;
    }

    public function isStartedAtLocalProvided(): bool
    {
        return $this->startedAtLocalProvided;
    }

    public function getStartedAtLocal(): ?\DateTimeImmutable
    {
        return $this->startedAtLocal;
    }

    public function setTimezone(?string $timezone): void
    {
        $this->timezoneProvided = true;
        $this->timezone = $timezone;
    }

    public function isTimezoneProvided(): bool
    {
        return $this->timezoneProvided;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setDistanceM(?float $distanceM): void
    {
        $this->distanceMProvided = true;
        $this->distanceM = $distanceM;
    }

    public function isDistanceMProvided(): bool
    {
        return $this->distanceMProvided;
    }

    public function getDistanceM(): ?float
    {
        return $this->distanceM;
    }

    public function setMovingTimeS(?int $movingTimeS): void
    {
        $this->movingTimeSProvided = true;
        $this->movingTimeS = $movingTimeS;
    }

    public function isMovingTimeSProvided(): bool
    {
        return $this->movingTimeSProvided;
    }

    public function getMovingTimeS(): ?int
    {
        return $this->movingTimeS;
    }

    public function setElapsedTimeS(?int $elapsedTimeS): void
    {
        $this->elapsedTimeSProvided = true;
        $this->elapsedTimeS = $elapsedTimeS;
    }

    public function isElapsedTimeSProvided(): bool
    {
        return $this->elapsedTimeSProvided;
    }

    public function getElapsedTimeS(): ?int
    {
        return $this->elapsedTimeS;
    }

    public function setElevationGainM(?float $elevationGainM): void
    {
        $this->elevationGainMProvided = true;
        $this->elevationGainM = $elevationGainM;
    }

    public function isElevationGainMProvided(): bool
    {
        return $this->elevationGainMProvided;
    }

    public function getElevationGainM(): ?float
    {
        return $this->elevationGainM;
    }

    public function setAverageSpeedMps(?float $averageSpeedMps): void
    {
        $this->averageSpeedMpsProvided = true;
        $this->averageSpeedMps = $averageSpeedMps;
    }

    public function isAverageSpeedMpsProvided(): bool
    {
        return $this->averageSpeedMpsProvided;
    }

    public function getAverageSpeedMps(): ?float
    {
        return $this->averageSpeedMps;
    }

    public function setMaxSpeedMps(?float $maxSpeedMps): void
    {
        $this->maxSpeedMpsProvided = true;
        $this->maxSpeedMps = $maxSpeedMps;
    }

    public function isMaxSpeedMpsProvided(): bool
    {
        return $this->maxSpeedMpsProvided;
    }

    public function getMaxSpeedMps(): ?float
    {
        return $this->maxSpeedMps;
    }

    public function setAverageHeartrate(?float $averageHeartrate): void
    {
        $this->averageHeartrateProvided = true;
        $this->averageHeartrate = $averageHeartrate;
    }

    public function isAverageHeartrateProvided(): bool
    {
        return $this->averageHeartrateProvided;
    }

    public function getAverageHeartrate(): ?float
    {
        return $this->averageHeartrate;
    }

    public function setMaxHeartrate(?float $maxHeartrate): void
    {
        $this->maxHeartrateProvided = true;
        $this->maxHeartrate = $maxHeartrate;
    }

    public function isMaxHeartrateProvided(): bool
    {
        return $this->maxHeartrateProvided;
    }

    public function getMaxHeartrate(): ?float
    {
        return $this->maxHeartrate;
    }

    public function setAverageWatts(?float $averageWatts): void
    {
        $this->averageWattsProvided = true;
        $this->averageWatts = $averageWatts;
    }

    public function isAverageWattsProvided(): bool
    {
        return $this->averageWattsProvided;
    }

    public function getAverageWatts(): ?float
    {
        return $this->averageWatts;
    }

    public function setMaxWatts(?float $maxWatts): void
    {
        $this->maxWattsProvided = true;
        $this->maxWatts = $maxWatts;
    }

    public function isMaxWattsProvided(): bool
    {
        return $this->maxWattsProvided;
    }

    public function getMaxWatts(): ?float
    {
        return $this->maxWatts;
    }

    public function setCalories(?float $calories): void
    {
        $this->caloriesProvided = true;
        $this->calories = $calories;
    }

    public function isCaloriesProvided(): bool
    {
        return $this->caloriesProvided;
    }

    public function getCalories(): ?float
    {
        return $this->calories;
    }

    public function setSummaryPolyline(?string $summaryPolyline): void
    {
        $this->summaryPolylineProvided = true;
        $this->summaryPolyline = $summaryPolyline;
    }

    public function isSummaryPolylineProvided(): bool
    {
        return $this->summaryPolylineProvided;
    }

    public function getSummaryPolyline(): ?string
    {
        return $this->summaryPolyline;
    }

    /**
     * @param array<string, mixed>|null $rawExternalSummary
     */
    public function setRawExternalSummary(?array $rawExternalSummary): void
    {
        $this->rawExternalSummaryProvided = true;
        $this->rawExternalSummary = $rawExternalSummary;
    }

    public function isRawExternalSummaryProvided(): bool
    {
        return $this->rawExternalSummaryProvided;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRawExternalSummary(): ?array
    {
        return $this->rawExternalSummary;
    }

    /**
     * @param array<string, mixed>|null $rawExternalDetail
     */
    public function setRawExternalDetail(?array $rawExternalDetail): void
    {
        $this->rawExternalDetailProvided = true;
        $this->rawExternalDetail = $rawExternalDetail;
    }

    public function isRawExternalDetailProvided(): bool
    {
        return $this->rawExternalDetailProvided;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRawExternalDetail(): ?array
    {
        return $this->rawExternalDetail;
    }

    public function setSyncedAt(?\DateTimeImmutable $syncedAt): void
    {
        $this->syncedAtProvided = true;
        $this->syncedAt = $syncedAt;
    }

    public function isSyncedAtProvided(): bool
    {
        return $this->syncedAtProvided;
    }

    public function getSyncedAt(): ?\DateTimeImmutable
    {
        return $this->syncedAt;
    }

    /**
     * @param list<array{0: float, 1: float}>|null $routeCoordinates
     */
    public function setRouteCoordinates(?array $routeCoordinates): void
    {
        $this->routeCoordinatesProvided = true;
        $this->routeCoordinates = $routeCoordinates;
    }

    public function isRouteCoordinatesProvided(): bool
    {
        return $this->routeCoordinatesProvided;
    }

    /**
     * @return list<array{0: float, 1: float}>|null
     */
    public function getRouteCoordinates(): ?array
    {
        return $this->routeCoordinates;
    }

    public function setStreamsSyncedAt(?\DateTimeImmutable $streamsSyncedAt): void
    {
        $this->streamsSyncedAtProvided = true;
        $this->streamsSyncedAt = $streamsSyncedAt;
    }

    public function isStreamsSyncedAtProvided(): bool
    {
        return $this->streamsSyncedAtProvided;
    }

    public function getStreamsSyncedAt(): ?\DateTimeImmutable
    {
        return $this->streamsSyncedAt;
    }

}
