<?php

namespace App\Dto\TrainingActivity;

use App\Enum\ActivitySource;
use App\Enum\SportType;

final class TrainingActivityDetailDto
{
    public string $id;

    public string $name;

    public SportType $sportType;

    public ActivitySource $source;

    public string $externalId;

    public string $startedAt;

    public ?string $startedAtLocal = null;

    public ?string $timezone = null;

    public ?float $distanceM = null;

    public ?int $movingTimeS = null;

    public ?int $elapsedTimeS = null;

    public ?float $elevationGainM = null;

    public ?float $averageSpeedMps = null;

    public ?float $maxSpeedMps = null;

    public ?float $averageHeartrate = null;

    public ?float $maxHeartrate = null;

    public ?float $averageWatts = null;

    public ?float $maxWatts = null;

    public ?float $calories = null;

    public bool $hasRoute = false;

    public ?string $syncedAt = null;

    public string $createdAt;

    public ?string $updatedAt = null;
}
