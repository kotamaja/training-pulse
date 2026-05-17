<?php

namespace App\Integration\Strava\Conversion;

use App\Dto\TrainingActivity\TrainingActivityCreateDto;
use App\Dto\TrainingActivity\TrainingActivityUpdateDto;

final readonly class StravaRouteStreamConverter
{
    /**
     * @param array<string, mixed> $streams
     */
    public function fillCreateDto(array $streams, TrainingActivityCreateDto $dto): void
    {
        $dto->streamsSyncedAt = new \DateTimeImmutable();

        $routeCoordinates = $this->toRouteCoordinatesOrNull($streams);

        if ($routeCoordinates === null) {
            return;
        }

        $dto->routeCoordinates = $routeCoordinates;
    }

    /**
     * @param array<string, mixed> $streams
     */
    public function fillUpdateDto(array $streams, TrainingActivityUpdateDto $dto): void
    {
        $dto->setStreamsSyncedAt(new \DateTimeImmutable());

        $routeCoordinates = $this->toRouteCoordinatesOrNull($streams);

        if ($routeCoordinates === null) {
            return;
        }

        $dto->setRouteCoordinates($routeCoordinates);
    }

    /**
     * Converts Strava latlng stream to GeoJSON coordinate order.
     *
     * Strava latlng:
     *   [latitude, longitude]
     *
     * TrainingPulse / GeoJSON:
     *   [longitude, latitude]
     *
     * @param array<string, mixed> $streams
     *
     * @return list<array{0: float, 1: float}>|null
     */
    private function toRouteCoordinatesOrNull(array $streams): ?array
    {
        $latlngStream = $streams['latlng'] ?? null;

        if (!is_array($latlngStream)) {
            return null;
        }

        $data = $latlngStream['data'] ?? null;

        if (!is_array($data) || $data === []) {
            return null;
        }

        $coordinates = [];

        foreach ($data as $point) {
            if (!is_array($point) || !isset($point[0], $point[1])) {
                continue;
            }

            $latitude = (float)$point[0];
            $longitude = (float)$point[1];

            $coordinates[] = [$longitude, $latitude];
        }

        if (count($coordinates) < 2) {
            return null;
        }

        return $coordinates;
    }
}
