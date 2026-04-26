<?php

namespace App\Mapper\CustomMapper\Athlete;

use App\Dto\Athlete\AthleteDetailDto;
use App\Entity\Athlete;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: Athlete::class, target: AthleteDetailDto::class)]
class AthleteToAthleteDetailDtoMapper implements CustomMapperInterface
{

    public function map(object $source): object
    {
        if (!$source instanceof Athlete) {
            throw new \InvalidArgumentException('Expected Athlete.');
        }

        $dto = new AthleteDetailDto();
        $dto->id = $source->getPublicId();
        $dto->displayName = $source->getDisplayName();
        $dto->birthYear = $source->getBirthYear();
        $dto->heightCm = $source->getHeightCm();
        $dto->weightKg = $source->getWeightKg();
        $dto->restingHeartRate = $source->getRestingHeartRate();
        $dto->maxHeartRate = $source->getMaxHeartRate();
        $dto->ftpWatts = $source->getFtpWatts();

        $dto->createdAt = $source->getCreatedAt()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');

        if ($source->getUpdatedAt()) {
            $dto->updatedAt = $source->getUpdatedAt()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
        }

        return $dto;

    }
}
