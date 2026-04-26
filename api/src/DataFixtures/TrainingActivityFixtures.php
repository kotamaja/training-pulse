<?php

namespace App\DataFixtures;

use App\Dto\TrainingActivity\TrainingActivityCreateDto;
use App\Entity\Athlete;
use App\Entity\User;
use App\Enum\ActivitySource;
use App\Enum\SportType;
use App\Write\TrainingActivity\TrainingActivityWriteServiceInterface;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TrainingActivityFixtures extends Fixture implements DependentFixtureInterface
{


    public function __construct(

        private readonly TrainingActivityWriteServiceInterface $trainingActivityWriteService,
    )
    {
    }


    /**
     * @param Athlete $athlete
     * @param User $actor
     * @return void
     * @throws \DateMalformedStringException
     */
    private function createRoadCyclingActivity(Athlete $athlete, User $actor): void
    {
        $input = new TrainingActivityCreateDto();

        $input->source = ActivitySource::Manual;
        $input->externalId = 'fixture-road-cycling-001';
        $input->name = 'Sortie vélo autour de Lausanne';
        $input->sportType = SportType::RoadCycling;

        $input->startedAt = new DateTimeImmutable('2026-04-20 08:30:00', new DateTimeZone('UTC'));
        $input->startedAtLocal = new DateTimeImmutable('2026-04-20 10:30:00', new DateTimeZone('Europe/Zurich'));
        $input->timezone = 'Europe/Zurich';

        $input->distanceM = 48250.0;
        $input->movingTimeS = 6420;
        $input->elapsedTimeS = 7200;
        $input->elevationGainM = 620.0;

        $input->averageSpeedMps = 7.52;
        $input->maxSpeedMps = 15.4;

        $input->averageHeartrate = 142.0;
        $input->maxHeartrate = 176.0;

        $input->averageWatts = 185.0;
        $input->maxWatts = 640.0;

        $input->calories = 1380.0;

        $input->routeCoordinates = [
            [6.63230, 46.51970], // Lausanne
            [6.64010, 46.52220],
            [6.65340, 46.52510],
            [6.66780, 46.51920],
            [6.68210, 46.51040],
            [6.69550, 46.50280],
            [6.70730, 46.49390],
            [6.71940, 46.48750],
            [6.73120, 46.48210],
            [6.74480, 46.47860],
        ];

        $input->rawExternalSummary = [
            'fixture' => true,
            'source' => 'manual',
            'kind' => 'road_cycling',
        ];

        $this->trainingActivityWriteService->create(
            input: $input,
            athlete: $athlete,
            actor: $actor,
        );
    }


    private function createNordicSkiSkatingActivity(\App\Entity\Athlete $athlete, \App\Entity\User $actor): void
    {
        $input = new TrainingActivityCreateDto();

        $input->source = ActivitySource::Manual;
        $input->externalId = 'fixture-nordic-ski-skating-001';
        $input->name = 'Ski de fond skating au Marchairuz';
        $input->sportType = SportType::NordicSkiingSkating;

        $input->startedAt = new \DateTimeImmutable('2026-02-08 09:00:00', new \DateTimeZone('UTC'));
        $input->startedAtLocal = new \DateTimeImmutable('2026-02-08 10:00:00', new \DateTimeZone('Europe/Zurich'));
        $input->timezone = 'Europe/Zurich';

        $input->distanceM = 18400.0;
        $input->movingTimeS = 5100;
        $input->elapsedTimeS = 5550;
        $input->elevationGainM = 320.0;

        $input->averageSpeedMps = 3.61;
        $input->maxSpeedMps = 8.2;

        $input->averageHeartrate = 151.0;
        $input->maxHeartrate = 181.0;

        $input->calories = 980.0;

        $input->routeCoordinates = [
            [6.24520, 46.55380],
            [6.24840, 46.55610],
            [6.25290, 46.55860],
            [6.25710, 46.56020],
            [6.26080, 46.55940],
            [6.26430, 46.55710],
            [6.26750, 46.55470],
            [6.26320, 46.55260],
            [6.25840, 46.55110],
            [6.25270, 46.55190],
            [6.24810, 46.55300],
            [6.24520, 46.55380],
        ];

        $input->rawExternalSummary = [
            'fixture' => true,
            'source' => 'manual',
            'kind' => 'nordic_skiing_skating',
        ];

        $this->trainingActivityWriteService->create(
            input: $input,
            athlete: $athlete,
            actor: $actor,
        );
    }

    private function createRowingActivity(\App\Entity\Athlete $athlete, \App\Entity\User $actor): void
    {
        $input = new TrainingActivityCreateDto();

        $input->source = ActivitySource::Manual;
        $input->externalId = 'fixture-rowing-001';
        $input->name = 'Aviron sur le Léman';
        $input->sportType = SportType::Rowing;

        $input->startedAt = new \DateTimeImmutable('2026-04-12 06:15:00', new \DateTimeZone('UTC'));
        $input->startedAtLocal = new \DateTimeImmutable('2026-04-12 08:15:00', new \DateTimeZone('Europe/Zurich'));
        $input->timezone = 'Europe/Zurich';

        $input->distanceM = 11200.0;
        $input->movingTimeS = 3300;
        $input->elapsedTimeS = 3600;
        $input->elevationGainM = 0.0;

        $input->averageSpeedMps = 3.39;
        $input->maxSpeedMps = 5.1;

        $input->averageHeartrate = 136.0;
        $input->maxHeartrate = 164.0;

        $input->calories = 720.0;

        $input->routeCoordinates = [
            [6.63910, 46.50680], // Ouchy approximatif
            [6.64520, 46.50410],
            [6.65260, 46.50120],
            [6.66130, 46.49810],
            [6.67040, 46.49520],
            [6.67910, 46.49280],
            [6.68740, 46.49060],
            [6.67900, 46.49220],
            [6.67010, 46.49470],
            [6.66090, 46.49760],
            [6.65180, 46.50080],
            [6.64440, 46.50370],
            [6.63910, 46.50680],
        ];

        $input->rawExternalSummary = [
            'fixture' => true,
            'source' => 'manual',
            'kind' => 'rowing',
        ];

        $this->trainingActivityWriteService->create(
            input: $input,
            athlete: $athlete,
            actor: $actor,
        );
    }

    private function createIndoorCyclingActivity(\App\Entity\Athlete $athlete, \App\Entity\User $actor): void
    {
        $input = new TrainingActivityCreateDto();

        $input->source = ActivitySource::Manual;
        $input->externalId = 'fixture-indoor-cycling-001';
        $input->name = 'Home trainer endurance';
        $input->sportType = SportType::IndoorCycling;

        $input->startedAt = new \DateTimeImmutable('2026-04-22 17:30:00', new \DateTimeZone('UTC'));
        $input->startedAtLocal = new \DateTimeImmutable('2026-04-22 19:30:00', new \DateTimeZone('Europe/Zurich'));
        $input->timezone = 'Europe/Zurich';

        $input->distanceM = 32500.0;
        $input->movingTimeS = 3600;
        $input->elapsedTimeS = 3600;
        $input->elevationGainM = null;

        $input->averageSpeedMps = 9.03;
        $input->maxSpeedMps = null;

        $input->averageHeartrate = 138.0;
        $input->maxHeartrate = 168.0;

        $input->averageWatts = 175.0;
        $input->maxWatts = 420.0;

        $input->calories = 810.0;

        // Important : indoor cycling, donc pas de route.
        $input->routeCoordinates = null;

        $input->rawExternalSummary = [
            'fixture' => true,
            'source' => 'manual',
            'kind' => 'indoor_cycling',
        ];

        $this->trainingActivityWriteService->create(
            input: $input,
            athlete: $athlete,
            actor: $actor,
        );
    }

    public function load(ObjectManager $manager): void
    {
        // TODO: Implement load() method.

        $me = $this->getReference("me-dev", User::class);
        $athlete = $this->getReference('athlete', Athlete::class);

        $this->createRoadCyclingActivity($athlete, $me);
        $this->createIndoorCyclingActivity($athlete, $me);
        $this->createNordicSkiSkatingActivity($athlete, $me);
        $this->createRowingActivity($athlete, $me);

        $manager->flush();
    }


    public function getDependencies(): array
    {
        return [
            AthleteFixtures::class,
        ];
    }


}
