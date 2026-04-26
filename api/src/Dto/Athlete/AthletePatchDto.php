<?php

namespace App\Dto\Athlete;

use Symfony\Component\Validator\Constraints as Assert;

final class AthletePatchDto
{
    private bool $displayNameProvided = false;
    private bool $birthYearProvided = false;
    private bool $heightCmProvided = false;
    private bool $weightKgProvided = false;
    private bool $restingHeartRateProvided = false;
    private bool $maxHeartRateProvided = false;
    private bool $ftpWattsProvided = false;

    #[Assert\Length(max: 180)]
    private ?string $displayName = null;

    #[Assert\Range(min: 1900, max: 2100)]
    private ?int $birthYear = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(300)]
    private ?float $heightCm = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(500)]
    private ?float $weightKg = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(250)]
    private ?int $restingHeartRate = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(250)]
    private ?int $maxHeartRate = null;

    #[Assert\Positive]
    #[Assert\LessThanOrEqual(1000)]
    private ?int $ftpWatts = null;

    public function setDisplayName(?string $displayName): void
    {
        $this->displayNameProvided = true;
        $this->displayName = $displayName;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function isDisplayNameProvided(): bool
    {
        return $this->displayNameProvided;
    }

    public function setBirthYear(?int $birthYear): void
    {
        $this->birthYearProvided = true;
        $this->birthYear = $birthYear;
    }

    public function getBirthYear(): ?int
    {
        return $this->birthYear;
    }

    public function isBirthYearProvided(): bool
    {
        return $this->birthYearProvided;
    }

    public function setHeightCm(?float $heightCm): void
    {
        $this->heightCmProvided = true;
        $this->heightCm = $heightCm;
    }

    public function getHeightCm(): ?float
    {
        return $this->heightCm;
    }

    public function isHeightCmProvided(): bool
    {
        return $this->heightCmProvided;
    }

    public function setWeightKg(?float $weightKg): void
    {
        $this->weightKgProvided = true;
        $this->weightKg = $weightKg;
    }

    public function getWeightKg(): ?float
    {
        return $this->weightKg;
    }

    public function isWeightKgProvided(): bool
    {
        return $this->weightKgProvided;
    }

    public function setRestingHeartRate(?int $restingHeartRate): void
    {
        $this->restingHeartRateProvided = true;
        $this->restingHeartRate = $restingHeartRate;
    }

    public function getRestingHeartRate(): ?int
    {
        return $this->restingHeartRate;
    }

    public function isRestingHeartRateProvided(): bool
    {
        return $this->restingHeartRateProvided;
    }

    public function setMaxHeartRate(?int $maxHeartRate): void
    {
        $this->maxHeartRateProvided = true;
        $this->maxHeartRate = $maxHeartRate;
    }

    public function getMaxHeartRate(): ?int
    {
        return $this->maxHeartRate;
    }

    public function isMaxHeartRateProvided(): bool
    {
        return $this->maxHeartRateProvided;
    }

    public function setFtpWatts(?int $ftpWatts): void
    {
        $this->ftpWattsProvided = true;
        $this->ftpWatts = $ftpWatts;
    }

    public function getFtpWatts(): ?int
    {
        return $this->ftpWatts;
    }

    public function isFtpWattsProvided(): bool
    {
        return $this->ftpWattsProvided;
    }
}
