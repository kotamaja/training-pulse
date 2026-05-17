<?php

namespace App\Write\TrainingActivity;

use App\Dto\TrainingActivity\TrainingActivityCreateDto;
use App\Dto\TrainingActivity\TrainingActivityUpdateDto;
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
    public function __construct(private EntityManagerInterface     $entityManager,
                                private TrainingActivityRepository $trainingActivityRepository)
    {
    }

    public function create(TrainingActivityCreateDto $input,
                           Athlete                   $athlete,
                           ?User                     $actor = null): TrainingActivity
    {
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

        $activity->setSummaryPolyline(
            $this->normalizeNullableString($input->summaryPolyline),
        );

        $activity->setRoute(
            $this->createLineString($input->routeCoordinates),
        );

        $activity->setRawExternalSummary($input->rawExternalSummary);
        $activity->setRawExternalDetail($input->rawExternalDetail);
        $activity->setSyncedAt($input->syncedAt);

        $activity->setStreamsSyncedAt($input->streamsSyncedAt);

        $this->entityManager->persist($activity);

        return $activity;
    }

    public function update(TrainingActivityUpdateDto $input,
                           TrainingActivity          $trainingActivity,
                           ?User                     $actor = null): TrainingActivityUpdateResult
    {
        $changed = false;

        /*
         * Validation croisée sur l'état final.
         *
         * Si un champ n'est pas fourni par le DTO d'update,
         * on reprend la valeur actuelle de l'entité.
         */
        $newMovingTimeS = $input->isMovingTimeSProvided()
            ? $input->getMovingTimeS()
            : $trainingActivity->getMovingTimeS();

        $newElapsedTimeS = $input->isElapsedTimeSProvided()
            ? $input->getElapsedTimeS()
            : $trainingActivity->getElapsedTimeS();

        $this->validateDurations(
            movingTimeS: $newMovingTimeS,
            elapsedTimeS: $newElapsedTimeS,
        );

        $newAverageHeartrate = $input->isAverageHeartrateProvided()
            ? $input->getAverageHeartrate()
            : $trainingActivity->getAverageHeartrate();

        $newMaxHeartrate = $input->isMaxHeartrateProvided()
            ? $input->getMaxHeartrate()
            : $trainingActivity->getMaxHeartrate();

        $this->validateHeartRates(
            averageHeartrate: $newAverageHeartrate,
            maxHeartrate: $newMaxHeartrate,
        );

        if ($input->isNameProvided()) {
            $name = $input->getName();

            if ($name === null || trim($name) === '') {
                throw new BusinessRuleViolationException(
                    message: 'Name cannot be empty.',
                    field: 'name',
                );
            }

            $name = trim($name);

            if ($trainingActivity->getName() !== $name) {
                $trainingActivity->setName($name);
                $changed = true;
            }
        }

        if ($input->isSportTypeProvided()) {
            $sportType = $input->getSportType();

            if ($sportType === null) {
                throw new BusinessRuleViolationException(
                    message: 'Sport type cannot be null.',
                    field: 'sportType',
                );
            }

            if ($trainingActivity->getSportType() !== $sportType) {
                $trainingActivity->setSportType($sportType);
                $changed = true;
            }
        }

        if ($input->isStartedAtProvided()) {
            $startedAt = $input->getStartedAt();

            if ($startedAt === null) {
                throw new BusinessRuleViolationException(
                    message: 'Start date cannot be null.',
                    field: 'startedAt',
                );
            }

            $startedAt = $startedAt->setTimezone(new \DateTimeZone('UTC'));


            if (!$this->sameInstant($trainingActivity->getStartedAt(), $startedAt)) {
                $trainingActivity->setStartedAt($startedAt);
                $changed = true;
            }
        }

        if ($input->isStartedAtLocalProvided()) {
            if (!$this->sameLocalDateTime($trainingActivity->getStartedAtLocal(), $input->getStartedAtLocal())) {
                $trainingActivity->setStartedAtLocal($input->getStartedAtLocal());
                $changed = true;
            }
        }

        if ($input->isTimezoneProvided()) {
            $timezone = $this->normalizeNullableString($input->getTimezone());

            if ($trainingActivity->getTimezone() !== $timezone) {
                $trainingActivity->setTimezone($timezone);
                $changed = true;
            }
        }

        if ($input->isDistanceMProvided()) {
            if ($trainingActivity->getDistanceM() !== $input->getDistanceM()) {
                $trainingActivity->setDistanceM($input->getDistanceM());
                $changed = true;
            }
        }

        if ($input->isMovingTimeSProvided()) {
            if ($trainingActivity->getMovingTimeS() !== $input->getMovingTimeS()) {
                $trainingActivity->setMovingTimeS($input->getMovingTimeS());
                $changed = true;
            }
        }

        if ($input->isElapsedTimeSProvided()) {
            if ($trainingActivity->getElapsedTimeS() !== $input->getElapsedTimeS()) {
                $trainingActivity->setElapsedTimeS($input->getElapsedTimeS());
                $changed = true;
            }
        }

        if ($input->isElevationGainMProvided()) {
            if ($trainingActivity->getElevationGainM() !== $input->getElevationGainM()) {
                $trainingActivity->setElevationGainM($input->getElevationGainM());
                $changed = true;
            }
        }

        if ($input->isAverageSpeedMpsProvided()) {
            if ($trainingActivity->getAverageSpeedMps() !== $input->getAverageSpeedMps()) {
                $trainingActivity->setAverageSpeedMps($input->getAverageSpeedMps());
                $changed = true;
            }
        }

        if ($input->isMaxSpeedMpsProvided()) {
            if ($trainingActivity->getMaxSpeedMps() !== $input->getMaxSpeedMps()) {
                $trainingActivity->setMaxSpeedMps($input->getMaxSpeedMps());
                $changed = true;
            }
        }

        if ($input->isAverageHeartrateProvided()) {
            if ($trainingActivity->getAverageHeartrate() !== $input->getAverageHeartrate()) {
                $trainingActivity->setAverageHeartrate($input->getAverageHeartrate());
                $changed = true;
            }
        }

        if ($input->isMaxHeartrateProvided()) {
            if ($trainingActivity->getMaxHeartrate() !== $input->getMaxHeartrate()) {
                $trainingActivity->setMaxHeartrate($input->getMaxHeartrate());
                $changed = true;
            }
        }

        if ($input->isAverageWattsProvided()) {
            if ($trainingActivity->getAverageWatts() !== $input->getAverageWatts()) {
                $trainingActivity->setAverageWatts($input->getAverageWatts());
                $changed = true;
            }
        }

        if ($input->isMaxWattsProvided()) {
            if ($trainingActivity->getMaxWatts() !== $input->getMaxWatts()) {
                $trainingActivity->setMaxWatts($input->getMaxWatts());
                $changed = true;
            }
        }

        if ($input->isCaloriesProvided()) {
            if ($trainingActivity->getCalories() !== $input->getCalories()) {
                $trainingActivity->setCalories($input->getCalories());
                $changed = true;
            }
        }

        if ($input->isSummaryPolylineProvided()) {
            $summaryPolyline = $this->normalizeNullableString($input->getSummaryPolyline());

            if ($trainingActivity->getSummaryPolyline() !== $summaryPolyline) {
                $trainingActivity->setSummaryPolyline($summaryPolyline);
                $changed = true;
            }
        }

        if ($input->isRawExternalSummaryProvided()) {
            if ($trainingActivity->getRawExternalSummary() !== $input->getRawExternalSummary()) {
                $trainingActivity->setRawExternalSummary($input->getRawExternalSummary());
                // Ne pas compter comme changement métier.
            }
        }

        if ($input->isRawExternalDetailProvided()) {
            if ($trainingActivity->getRawExternalDetail() !== $input->getRawExternalDetail()) {
                $trainingActivity->setRawExternalDetail($input->getRawExternalDetail());
                // Ne pas compter comme changement métier.
            }
        }

        if ($input->isSyncedAtProvided()) {
            if ($trainingActivity->getSyncedAt() != $input->getSyncedAt()) {
                $trainingActivity->setSyncedAt($input->getSyncedAt());

                // Ne pas passer $changed à true :
                // syncedAt indique seulement que l'activité a été vue pendant la synchro.
            }
        }

        if ($input->isRouteCoordinatesProvided()) {
            $route = $this->createLineString($input->getRouteCoordinates());

            if (!$this->sameRoute($trainingActivity->getRoute(), $route)) {
                $trainingActivity->setRoute($route);
                $changed = true;
            }
        }

        if ($input->isStreamsSyncedAtProvided()) {
            if (!$this->sameInstant($trainingActivity->getStreamsSyncedAt(), $input->getStreamsSyncedAt())) {
                $trainingActivity->setStreamsSyncedAt($input->getStreamsSyncedAt());

                // Je ne mettrais pas $changed = true ici.
                // Comme syncedAt, c'est une info technique de synchronisation.
            }
        }

        return new TrainingActivityUpdateResult(
            trainingActivity: $trainingActivity,
            changed: $changed,
        );
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
     * Coordinates are expected in GeoJSON order: [longitude, latitude].
     *
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

            $longitude = (float)$coordinate[0];
            $latitude = (float)$coordinate[1];

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

            /*
             * With longitude-one/spatial-types, keep the Point simple.
             * The SRID, if used, belongs to the LineString.
             *
             * In our MySQL schema we currently use:
             *   route LINESTRING DEFAULT NULL
             *
             * and we enforce WGS84 / lon-lat at application level.
             */
            $points[] = new Point($longitude, $latitude);
        }

        return new LineString($points, 4326);
    }


    private function sameInstant(?\DateTimeImmutable $left, ?\DateTimeImmutable $right): bool
    {
        if ($left === null || $right === null) {
            return $left === $right;
        }

        return $left->getTimestamp() === $right->getTimestamp();
    }


    private function sameLocalDateTime(?\DateTimeImmutable $left, ?\DateTimeImmutable $right): bool
    {
        if ($left === null || $right === null) {
            return $left === $right;
        }

        return $left->format('Y-m-d H:i:s') === $right->format('Y-m-d H:i:s');
    }

    private function sameRoute(?LineString $left, ?LineString $right): bool
    {
        if ($left === null || $right === null) {
            return $left === $right;
        }

        $leftPoints = $left->getPoints();
        $rightPoints = $right->getPoints();

        if (count($leftPoints) !== count($rightPoints)) {
            return false;
        }

        foreach ($leftPoints as $index => $leftPoint) {
            $rightPoint = $rightPoints[$index];

            if (abs($leftPoint->getX() - $rightPoint->getX()) > 0.0000001) {
                return false;
            }

            if (abs($leftPoint->getY() - $rightPoint->getY()) > 0.0000001) {
                return false;
            }
        }

        return true;
    }
}
