<?php

namespace App\Command\Strava;

use App\Integration\Strava\Api\StravaApiClient;
use App\Integration\Strava\Auth\StravaTokenManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:strava:test-activity-streams',
    description: 'Fetch and inspect Strava activity streams for a given Strava activity id.',
)]
final class StravaTestActivityStreamsCommand extends Command
{

    public function __construct(private readonly StravaCommandAccountResolver $accountResolver,
                                private readonly StravaTokenManager           $tokenManager,
                                private readonly StravaApiClient              $stravaApiClient,)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'email',
            null,
            InputOption::VALUE_REQUIRED,
            'TrainingPulse user email.',
        )
            ->addArgument(
                'activity-id',
                InputArgument::REQUIRED,
                'The Strava activity id.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getOption('email');

        if (!is_string($email) || trim($email) === '') {
            $io->error('Missing required option --email.');

            return Command::FAILURE;
        }

        /** @var string $activityId */
        $activityId = (string)$input->getArgument('activity-id');

        $user = $this->accountResolver->resolveUserByEmail($email);
        $athlete = $user->requireAthlete();
        $externalAccount = $this->accountResolver->resolveStravaAccountByEmail($email);

        if ($externalAccount === null) {
            $io->error(sprintf(
                'No Strava external account found for athlete "%s".',
                $athlete->getDisplayName(),
            ));

            return Command::FAILURE;
        }

        $accessToken = $this->tokenManager->getValidAccessToken($externalAccount);

        $streams = $this->stravaApiClient->getActivityStreams(
            accessToken: $accessToken,
            activityId: $activityId,
            keys: ['latlng'],
        );

        $latlngStream = $streams['latlng'] ?? null;

        if (!is_array($latlngStream)) {
            $io->warning('No "latlng" stream was returned by Strava for this activity.');

            $io->section('Returned stream keys');
            $io->listing(array_keys($streams));

            return Command::SUCCESS;
        }

        $data = $latlngStream['data'] ?? null;

        if (!is_array($data)) {
            $io->warning('The "latlng" stream does not contain a valid "data" array.');

            return Command::SUCCESS;
        }

        $pointCount = count($data);

        $io->success('Strava latlng stream fetched successfully.');

        $io->definitionList(
            ['TrainingPulse user' => $user->getEmail()],
            ['TrainingPulse athlete' => $athlete->getDisplayName()],
            ['Strava account id' => $externalAccount->getProviderAccountId()],
            ['Strava activity id' => $activityId],
            ['Returned stream keys' => implode(', ', array_keys($streams))],
            ['Lat/lng points' => (string)$pointCount],
        );

        if ($pointCount === 0) {
            $io->warning('The latlng stream is empty.');

            return Command::SUCCESS;
        }

        $firstPoints = array_slice($data, 0, 3);
        $lastPoints = array_slice($data, -3);

        $io->section('First points returned by Strava');
        $this->printLatLngRows($io, $firstPoints);

        $io->section('Last points returned by Strava');
        $this->printLatLngRows($io, $lastPoints);

        $io->note([
            'Strava latlng points are usually [latitude, longitude].',
            'TrainingPulse routeCoordinates must be [longitude, latitude] for GeoJSON / LineString usage.',
        ]);

        return Command::SUCCESS;
    }


    /**
     * @param list<mixed> $points
     */
    private function printLatLngRows(SymfonyStyle $io, array $points): void
    {
        $rows = [];

        foreach ($points as $index => $point) {
            if (!is_array($point) || !isset($point[0], $point[1])) {
                $rows[] = [
                    'index' => (string)$index,
                    'lat' => 'invalid',
                    'lng' => 'invalid',
                    'geojson' => 'invalid',
                ];

                continue;
            }

            $latitude = (float)$point[0];
            $longitude = (float)$point[1];

            $rows[] = [
                'index' => (string)$index,
                'lat' => (string)$latitude,
                'lng' => (string)$longitude,
                'geojson' => sprintf('[%F, %F]', $longitude, $latitude),
            ];
        }

        $io->table(
            ['index', 'lat', 'lng', 'GeoJSON [lng, lat]'],
            $rows,
        );
    }
}
