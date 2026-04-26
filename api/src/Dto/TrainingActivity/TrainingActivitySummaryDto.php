<?php

namespace App\Dto\TrainingActivity;

use App\Enum\SportType;

final class TrainingActivitySummaryDto
{
    public string $id;

    public string $name;

    public SportType $sportType;

    public string $startedAt;

    public ?string $startedAtLocal = null;

    public ?string $timezone = null;

    public ?float $distanceM = null;

    public ?int $movingTimeS = null;

    public ?float $elevationGainM = null;

    public ?float $averageHeartrate = null;

    public ?float $averageWatts = null;

    public bool $hasRoute = false;
}
