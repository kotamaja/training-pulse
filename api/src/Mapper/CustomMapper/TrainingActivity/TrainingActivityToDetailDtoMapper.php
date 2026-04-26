<?php

namespace App\Mapper\CustomMapper\TrainingActivity;

use App\Dto\TrainingActivity\TrainingActivityDetailDto;
use App\Entity\TrainingActivity;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(
    source: TrainingActivity::class,
    target: TrainingActivityDetailDto::class,
)]
final readonly class TrainingActivityToDetailDtoMapper implements CustomMapperInterface
{
    public function map(object $source): object
    {
        if (!$source instanceof TrainingActivity) {
            throw new \InvalidArgumentException(sprintf(
                'Expected "%s", got "%s".',
                TrainingActivity::class,
                $source::class,
            ));
        }

        $dto = new TrainingActivityDetailDto();

        $dto->id = $source->getPublicId();

        $dto->name = $source->getName();
        $dto->sportType = $source->getSportType();

        $dto->source = $source->getSource()->value;
        $dto->externalId = $source->getExternalId();

        // Instant absolu, toujours en UTC.
        $dto->startedAt = $source
            ->getStartedAt()
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');

        // Heure locale "murale", sans offset ni Z.
        // Exemple: "2026-04-20T05:00:00"
        $dto->startedAtLocal = $source
            ->getStartedAtLocal()
            ?->format('Y-m-d\TH:i:s');

        // Fuseau IANA si connu, par exemple "Europe/Zurich".
        $dto->timezone = $source->getTimezone();

        $dto->distanceM = $source->getDistanceM();

        $dto->movingTimeS = $source->getMovingTimeS();
        $dto->elapsedTimeS = $source->getElapsedTimeS();

        $dto->elevationGainM = $source->getElevationGainM();

        $dto->averageSpeedMps = $source->getAverageSpeedMps();
        $dto->maxSpeedMps = $source->getMaxSpeedMps();

        $dto->averageHeartrate = $source->getAverageHeartrate();
        $dto->maxHeartrate = $source->getMaxHeartrate();

        $dto->averageWatts = $source->getAverageWatts();
        $dto->maxWatts = $source->getMaxWatts();

        $dto->calories = $source->getCalories();

        $dto->hasRoute = $source->hasRoute();

        $dto->syncedAt = $source
            ->getSyncedAt()
            ?->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');

        $dto->createdAt = $source
            ->getCreatedAt()
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');

        $dto->updatedAt = $source
            ->getUpdatedAt()
            ?->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');

        return $dto;
    }
}
