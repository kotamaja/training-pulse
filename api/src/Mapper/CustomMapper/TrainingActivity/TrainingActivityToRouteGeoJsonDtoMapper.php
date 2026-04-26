<?php

namespace App\Mapper\CustomMapper\TrainingActivity;

use App\Dto\TrainingActivity\TrainingActivityRouteGeoJsonDto;
use App\Entity\TrainingActivity;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;
use App\Write\Exception\BusinessRuleViolationException;

#[Maps(
    source: TrainingActivity::class,
    target: TrainingActivityRouteGeoJsonDto::class,
)]
final class TrainingActivityToRouteGeoJsonDtoMapper  implements CustomMapperInterface
{
    public function map(object $source): object
    {
        if (!$source instanceof TrainingActivity) {
            throw new \InvalidArgumentException('Expected TrainingActivity.');
        }


        $route = $source->getRoute();

        if ($route === null) {
            throw new BusinessRuleViolationException(
                message: 'No route is available for this training activity.',
                field: 'route',
            );
        }

        $coordinates = [];

        foreach ($route->getPoints() as $point) {
            $coordinates[] = [
                $point->getX(), // longitude
                $point->getY(), // latitude
            ];
        }

        $dto = new TrainingActivityRouteGeoJsonDto();

        $dto->geometry = [
            'type' => 'LineString',
            'coordinates' => $coordinates,
        ];

        $dto->properties = [
            'id' => $source->getPublicId(),
            'name' => $source->getName(),
            'sportType' => $source->getSportType()->value,
            'startedAt' => $source
                ->getStartedAt()
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s\Z'),
            'startedAtLocal' => $source
                ->getStartedAtLocal()
                ?->format('Y-m-d\TH:i:s'),
            'timezone' => $source->getTimezone(),
            'distanceM' => $source->getDistanceM(),
        ];

        return $dto;
    }
}
