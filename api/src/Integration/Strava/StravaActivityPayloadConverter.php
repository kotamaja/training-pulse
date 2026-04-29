<?php

namespace App\Integration\Strava;

use App\Dto\TrainingActivity\TrainingActivityCreateDto;
use App\Dto\TrainingActivity\TrainingActivityUpdateDto;
use App\Enum\ActivitySource;
use App\Integration\ActivityProvider\ExternalActivityIdentity;
use App\Integration\Support\ExternalPayloadReader;

final readonly class StravaActivityPayloadConverter
{
    public function __construct(
        private StravaSportTypeConverter $sportTypeConverter,
        private ExternalPayloadReader    $reader,
    )
    {
    }


    public function extractIdentity(array $activity): ExternalActivityIdentity
    {
        return new ExternalActivityIdentity(
            source: ActivitySource::Strava,
            externalId: $this->reader->requireString($activity, 'id', 'Strava activity'),
        );
    }

    /**
     * @param array<string, mixed> $activity
     */
    public function toCreateDto(array $activity): TrainingActivityCreateDto
    {
        $dto = new TrainingActivityCreateDto();

        $dto->source = ActivitySource::Strava;
        $dto->externalId = $this->reader->requireString($activity, 'id', 'Strava activity');

        $this->fillCreateDto($activity, $dto);

        return $dto;
    }

    /**
     * @param array<string, mixed> $activity
     */
    public function toUpdateDto(array $activity): TrainingActivityUpdateDto
    {
        $dto = new TrainingActivityUpdateDto();

        $this->fillUpdateDto($activity, $dto);

        return $dto;
    }

    private function fillCreateDto(array $activity, TrainingActivityCreateDto $dto): void
    {
        $dto->name = $this->reader->stringOrDefault($activity, 'name', 'Untitled activity');

        $stravaSportType = $this->reader->optionalString($activity, 'sport_type')
            ?? $this->reader->optionalString($activity, 'type')
            ?? 'Other';

        $dto->sportType = $this->sportTypeConverter->convert($stravaSportType);

        $dto->startedAt = new \DateTimeImmutable(
            $this->reader->requireString($activity, 'start_date', 'Strava activity'),
        );

        $startDateLocal = $this->reader->optionalString($activity, 'start_date_local');
        $dto->startedAtLocal = $startDateLocal !== null
            ? new \DateTimeImmutable($startDateLocal)
            : null;

        $dto->timezone = $this->extractTimezone(
            $this->reader->optionalString($activity, 'timezone'),
        );

        $dto->distanceM = $this->reader->optionalFloat($activity, 'distance');
        $dto->movingTimeS = $this->reader->optionalInt($activity, 'moving_time');
        $dto->elapsedTimeS = $this->reader->optionalInt($activity, 'elapsed_time');
        $dto->elevationGainM = $this->reader->optionalFloat($activity, 'total_elevation_gain');

        $dto->averageSpeedMps = $this->reader->optionalFloat($activity, 'average_speed');
        $dto->maxSpeedMps = $this->reader->optionalFloat($activity, 'max_speed');

        $dto->averageHeartrate = $this->reader->optionalFloat($activity, 'average_heartrate');
        $dto->maxHeartrate = $this->reader->optionalFloat($activity, 'max_heartrate');

        $dto->averageWatts = $this->reader->optionalFloat($activity, 'average_watts');
        $dto->maxWatts = $this->reader->optionalFloat($activity, 'max_watts');

        $dto->calories = $this->reader->optionalFloat($activity, 'calories');

        $dto->summaryPolyline = $this->reader->optionalStringPath($activity, ['map', 'summary_polyline']);

        $dto->routeCoordinates = null;
        $dto->rawExternalSummary = $activity;
        $dto->rawExternalDetail = null;
        $dto->syncedAt = new \DateTimeImmutable();
    }


    private function fillUpdateDto(array $activity, TrainingActivityUpdateDto $dto): void
    {
        if (array_key_exists('name', $activity)) {
            $dto->setName($this->reader->stringOrDefault($activity, 'name', 'Untitled activity'));
        }

        if (array_key_exists('sport_type', $activity) || array_key_exists('type', $activity)) {
            $stravaSportType = $this->reader->optionalString($activity, 'sport_type')
                ?? $this->reader->optionalString($activity, 'type')
                ?? 'Other';

            $dto->setSportType($this->sportTypeConverter->convert($stravaSportType));
        }

        if (array_key_exists('start_date', $activity)) {
            $dto->setStartedAt(new \DateTimeImmutable(
                $this->reader->requireString($activity, 'start_date', 'Strava activity'),
            )->setTimezone(new \DateTimeZone('UTC')),
            );
        }

        if (array_key_exists('start_date_local', $activity)) {
            $startDateLocal = $this->reader->optionalString($activity, 'start_date_local');


            if ($startDateLocal !== null) {
                $dto->setStartedAtLocal(new \DateTimeImmutable($startDateLocal));
            }
        }

        if (array_key_exists('timezone', $activity)) {
            $dto->setTimezone(
                $this->extractTimezone($this->reader->optionalString($activity, 'timezone')),
            );
        }

        if (array_key_exists('distance', $activity)) {
            $dto->setDistanceM($this->reader->optionalFloat($activity, 'distance'));
        }

        if (array_key_exists('moving_time', $activity)) {
            $dto->setMovingTimeS($this->reader->optionalInt($activity, 'moving_time'));
        }

        if (array_key_exists('elapsed_time', $activity)) {
            $dto->setElapsedTimeS($this->reader->optionalInt($activity, 'elapsed_time'));
        }

        if (array_key_exists('total_elevation_gain', $activity)) {
            $dto->setElevationGainM($this->reader->optionalFloat($activity, 'total_elevation_gain'));
        }

        if (array_key_exists('average_speed', $activity)) {
            $dto->setAverageSpeedMps($this->reader->optionalFloat($activity, 'average_speed'));
        }

        if (array_key_exists('max_speed', $activity)) {
            $dto->setMaxSpeedMps($this->reader->optionalFloat($activity, 'max_speed'));
        }

        if (array_key_exists('average_heartrate', $activity)) {
            $dto->setAverageHeartrate($this->reader->optionalFloat($activity, 'average_heartrate'));
        }

        if (array_key_exists('max_heartrate', $activity)) {
            $dto->setMaxHeartrate($this->reader->optionalFloat($activity, 'max_heartrate'));
        }

        if (array_key_exists('average_watts', $activity)) {
            $dto->setAverageWatts($this->reader->optionalFloat($activity, 'average_watts'));
        }

        if (array_key_exists('max_watts', $activity)) {
            $dto->setMaxWatts($this->reader->optionalFloat($activity, 'max_watts'));
        }

        if (array_key_exists('calories', $activity)) {
            $dto->setCalories($this->reader->optionalFloat($activity, 'calories'));
        }

        if (array_key_exists('map', $activity)) {
            $dto->setSummaryPolyline(
                $this->reader->optionalStringPath($activity, ['map', 'summary_polyline']),
            );
        }

        $dto->setRawExternalSummary($activity);
        $dto->setSyncedAt(new \DateTimeImmutable());
    }

    private function extractTimezone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Strava renvoie souvent: "(GMT+01:00) Europe/Zurich"
        if (preg_match('/\)\s*(.+)$/', $value, $matches) === 1) {
            $timezone = trim($matches[1]);

            return $timezone !== '' ? $timezone : null;
        }

        return $value;
    }
}
