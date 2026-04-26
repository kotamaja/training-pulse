<?php

namespace App\Dto\Athlete;

use Symfony\Component\Validator\Constraints as Assert;

final class AthleteCreateDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    public string $displayName;

    #[Assert\Range(min: 1900, max: 2100)]
    public ?int $birthYear = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(300)]
    public ?float $heightCm = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(500)]
    public ?float $weightKg = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(250)]
    public ?int $restingHeartRate = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(250)]
    public ?int $maxHeartRate = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(1000)]
    public ?int $ftpWatts = null;
}
