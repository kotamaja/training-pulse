<?php

namespace App\Mapper\CustomMapper\TrainingActivity;

use App\Dto\TrainingActivity\TrainingActivitySummaryDto;
use App\Entity\TrainingActivity;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: TrainingActivity::class, target: TrainingActivitySummaryDto::class)]
final class TrainingActivityToSummaryDtoMapper implements CustomMapperInterface
{

    public function map(object $source): object
    {
        if (!$source instanceof TrainingActivity) {
            throw new \InvalidArgumentException('Expected TrainingActivity.');
        }

        $dto = new TrainingActivitySummaryDto();
        $dto->id = $source->getPublicId();
        $dto->name = $source->getName();
        $dto->sportType = $source->getSportType();
        $dto->startedAt = $source
            ->getStartedAt()
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');

        $dto->startedAtLocal = $source
            ->getStartedAtLocal()
            ?->format('Y-m-d\TH:i:s');

        $dto->timezone = $source->getTimezone();


        $dto->distanceM = $source->getDistanceM();
        $dto->movingTimeS = $source->getMovingTimeS();
        $dto->elevationGainM = $source->getElevationGainM();
        $dto->averageHeartrate = $source->getAverageHeartrate();
        $dto->averageWatts = $source->getAverageWatts();
        $dto->hasRoute = $source->hasRoute();


        $dto->distanceM = $source->getDistanceM();
        $dto->movingTimeS = $source->getMovingTimeS();
        $dto->elevationGainM = $source->getElevationGainM();
        $dto->averageHeartrate = $source->getAverageHeartrate();
        $dto->averageWatts = $source->getAverageWatts();
        $dto->hasRoute = $source->hasRoute();
        return $dto;

    }
}
