<?php

namespace App\Write\TrainingActivity;

use App\Dto\TrainingActivity\TrainingActivityCreateDto;
use App\Entity\Athlete;
use App\Entity\TrainingActivity;
use App\Entity\User;
use App\Repository\TrainingActivityRepository;
use App\Write\Exception\BusinessRuleViolationException;
use App\Write\Exception\ResourceConflictException;
use Doctrine\ORM\EntityManagerInterface;
use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

final readonly class TrainingActivityWriteService implements TrainingActivityWriteServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TrainingActivityRepository $trainingActivityRepository,
    ) {
    }

    public function create(
        TrainingActivityCreateDto $input,
        Athlete $athlete,
        ?User $actor = null,
    ): TrainingActivity {
        $externalId = trim($input->externalId);
        if ($externalId === '') {
            throw new BusinessRuleViolationException(
                message: 'External id cannot be empty.',
                field: 'externalId',
            );
        }

        $name = trim($input->name);
        if ($name === '') {
            throw new BusinessRuleViolationException(
                message: 'Name cannot be empty.',
                field: 'name',
            );
        }

        $existingActivity = $this->trainingActivityRepository->findOneBy([
            'athlete' => $athlete,
            'source' => $input->source,
            'externalId' => $externalId,
        ]);

        if ($existingActivity !== null) {
            throw new ResourceConflictException(
                message: sprintf(
                    'A training activity already exists for source "%s" and external id "%s".',
                    $input->source->value,
                    $externalId,
                ),
                field: 'externalId',
            );
        }

        $this->validateDurations(
            movingTimeS: $input->movingTimeS,
            elapsedTimeS: $input->elapsedTimeS,
        );

        $this->validateHeartRates(
            averageHeartrate: $input->averageHeartrate,
            maxHeartrate: $input->maxHeartrate,
        );

        $activity = new TrainingActivity(
            athlete: $athlete,
            source: $input->source,
            externalId: $externalId,
            name: $name,
            sportType: $input->sportType,
            startedAt: $input->startedAt,
        );

        $activity->setStartedAtLocal($input->startedAtLocal);
        $activity->setTimezone($this->normalizeNullableString($input->timezone));

        $activity->setDistanceM($input->distanceM);
        $activity->setMovingTimeS($input->movingTimeS);
        $activity->setElapsedTimeS($input->elapsedTimeS);
        $activity->setElevationGainM($input->elevationGainM);

        $activity->setAverageSpeedMps($input->averageSpeedMps);
        $activity->setMaxSpeedMps($input->maxSpeedMps);

        $activity->setAverageHeartrate($input->averageHeartrate);
        $activity->setMaxHeartrate($input->maxHeartrate);

        $activity->setAverageWatts($input->averageWatts);
        $activity->setMaxWatts($input->maxWatts);

        $activity->setCalories($input->calories);
        $activity->setSummaryPolyline($this->normalizeNullableString($input->summaryPolyline));
        $activity->setRoute($this->createLineString($input->routeCoordinates));

        $activity->setRawExternalSummary($input->rawExternalSummary);
        $activity->setRawExternalDetail($input->rawExternalDetail);
        $activity->setSyncedAt($input->syncedAt);

        $this->entityManager->persist($activity);

        return $activity;
    }

    public function delete(TrainingActivity $trainingActivity, ?User $actor = null): void
    {
        $this->entityManager->remove($trainingActivity);
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function validateDurations(?int $movingTimeS, ?int $elapsedTimeS): void
    {
        if ($movingTimeS !== null && $elapsedTimeS !== null && $movingTimeS > $elapsedTimeS) {
            throw new BusinessRuleViolationException(
                message: 'Moving time cannot be greater than elapsed time.',
                field: 'movingTimeS',
            );
        }
    }

    private function validateHeartRates(?float $averageHeartrate, ?float $maxHeartrate): void
    {
        if ($averageHeartrate !== null && $maxHeartrate !== null && $averageHeartrate > $maxHeartrate) {
            throw new BusinessRuleViolationException(
                message: 'Average heart rate cannot be greater than max heart rate.',
                field: 'averageHeartrate',
            );
        }
    }

    /**
     * @param list<array{0: float, 1: float}>|null $coordinates
     */
    private function createLineString(?array $coordinates): ?LineString
    {
        if ($coordinates === null || $coordinates === []) {
            return null;
        }

        if (count($coordinates) < 2) {
            throw new BusinessRuleViolationException(
                message: 'Route must contain at least two points.',
                field: 'routeCoordinates',
            );
        }

        $points = [];

        foreach ($coordinates as $index => $coordinate) {
            if (!isset($coordinate[0], $coordinate[1])) {
                throw new BusinessRuleViolationException(
                    message: sprintf('Route point #%d must contain longitude and latitude.', $index),
                    field: 'routeCoordinates',
                );
            }

            $longitude = (float) $coordinate[0];
            $latitude = (float) $coordinate[1];

            if ($longitude < -180 || $longitude > 180) {
                throw new BusinessRuleViolationException(
                    message: sprintf('Longitude %.8F is out of range.', $longitude),
                    field: 'routeCoordinates',
                );
            }

            if ($latitude < -90 || $latitude > 90) {
                throw new BusinessRuleViolationException(
                    message: sprintf('Latitude %.8F is out of range.', $latitude),
                    field: 'routeCoordinates',
                );
            }

            $points[] = new Point($longitude, $latitude);
        }

        return new LineString($points, 4326);
    }
}
