<?php

namespace App\Dto\Athlete;

final class AthleteDetailDto
{
    public string $id;

    public string $displayName;

    public ?int $birthYear = null;

    public ?float $heightCm = null;

    public ?float $weightKg = null;

    public ?int $restingHeartRate = null;

    public ?int $maxHeartRate = null;

    public ?int $ftpWatts = null;

    public string $createdAt;

    public ?string $updatedAt = null;
}
