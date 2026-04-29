<?php

namespace App\Command\Strava;

use App\Entity\User;
use App\Enum\ActivitySource;
use App\Integration\Strava\StravaApiClient;
use App\Integration\Strava\StravaTokenManager;
use App\Repository\AthleteExternalAccountRepository;
use App\Repository\UserRepository;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:strava:list-activities',
    description: 'List recent Strava activities for the connected dev user account.',
)]
final class StravaListActivitiesCommand extends Command
{
    private const DEV_USER_EMAIL = 'dev@trainingpulse.local';

    public function __construct(private readonly UserRepository                   $userRepository,
                                private readonly AthleteExternalAccountRepository $externalAccountRepository,
                                private readonly StravaTokenManager               $tokenManager,
                                private readonly StravaApiClient                  $stravaApiClient,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'page',
                null,
                InputOption::VALUE_REQUIRED,
                'Strava page number.',
                1,
            )
            ->addOption(
                'per-page',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of activities per page.',
                10,
            )
            ->addOption(
                'after',
                null,
                InputOption::VALUE_REQUIRED,
                'Only return activities after this date, format YYYY-MM-DD.',
            )
            ->addOption(
                'before',
                null,
                InputOption::VALUE_REQUIRED,
                'Only return activities before this date, format YYYY-MM-DD.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $page = max(1, (int)$input->getOption('page'));
        $perPage = min(100, max(1, (int)$input->getOption('per-page')));

        $after = $this->parseDateOption($input->getOption('after'), 'after');
        $before = $this->parseDateOption($input->getOption('before'), 'before');

        $user = $this->findDevUser();
        $athlete = $user->requireAthlete();

        $externalAccount = $this->externalAccountRepository->findOneForAthleteAndProvider(
            athlete: $athlete,
            provider: ActivitySource::Strava,
        );

        if ($externalAccount === null) {
            $io->error(sprintf(
                'No Strava external account found for athlete "%s".',
                $athlete->getDisplayName(),
            ));

            return Command::FAILURE;
        }

        $accessToken = $this->tokenManager->getValidAccessToken($externalAccount);

        $activities = $this->stravaApiClient->listAthleteActivities(
            accessToken: $accessToken,
            page: $page,
            perPage: $perPage,
            after: $after,
            before: $before,
        );

        $io->title('Recent Strava activities');

        $io->definitionList(
            ['TrainingPulse user' => $user->getEmail()],
            ['TrainingPulse athlete' => $athlete->getDisplayName()],
            ['Strava account id' => $externalAccount->getProviderAccountId()],
            ['Page' => (string)$page],
            ['Per page' => (string)$perPage],
            ['Returned activities' => (string)count($activities)],
        );

        if ($activities === []) {
            $io->warning('No activities returned by Strava for these parameters.');

            return Command::SUCCESS;
        }

        $rows = [];

        foreach ($activities as $activity) {
            $rows[] = [
                'id' => $this->stringValue($activity['id'] ?? null),
                'name' => $this->stringValue($activity['name'] ?? null),
                'sport_type' => $this->stringValue($activity['sport_type'] ?? $activity['type'] ?? null),
                'start_date' => $this->stringValue($activity['start_date'] ?? null),
                'start_date_local' => $this->stringValue($activity['start_date_local'] ?? null),
                'timezone' => $this->stringValue($activity['timezone'] ?? null),
                'distance_m' => $this->numberValue($activity['distance'] ?? null),
                'moving_s' => $this->numberValue($activity['moving_time'] ?? null),
                'private' => $this->boolValue($activity['private'] ?? null),
                'has_polyline' => $this->hasSummaryPolyline($activity) ? 'yes' : 'no',
            ];
        }

        $io->table(
            [
                'id',
                'name',
                'sport_type',
                'start_date',
                'start_date_local',
                'timezone',
                'distance_m',
                'moving_s',
                'private',
                'has_polyline',
            ],
            $rows,
        );

        $io->note([
            'This command only reads Strava data.',
            'Nothing is imported into TrainingPulse yet.',
        ]);

        return Command::SUCCESS;
    }

    private function findDevUser(): User
    {
        $user = $this->userRepository->findOneBy([
            'email' => self::DEV_USER_EMAIL,
        ]);

        if (!$user instanceof User) {
            throw new RuntimeException(sprintf(
                'Dev user "%s" was not found.',
                self::DEV_USER_EMAIL,
            ));
        }

        return $user;
    }

    private function parseDateOption(mixed $value, string $name): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('Option "%s" must be a string.', $name));
        }

        $date = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $value,
            new \DateTimeZone('UTC'),
        );

        if (!$date instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid "%s" date "%s". Expected YYYY-MM-DD.',
                $name,
                $value,
            ));
        }

        return $date;
    }

    private function stringValue(mixed $value): string
    {
        if ($value === null) {
            return 'n/a';
        }

        if (is_scalar($value)) {
            return (string)$value;
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    private function numberValue(mixed $value): string
    {
        if (is_int($value) || is_float($value)) {
            return (string)round((float)$value, 2);
        }

        if (is_string($value) && is_numeric($value)) {
            return (string)round((float)$value, 2);
        }

        return 'n/a';
    }

    private function boolValue(mixed $value): string
    {
        if ($value === true) {
            return 'yes';
        }

        if ($value === false) {
            return 'no';
        }

        return 'n/a';
    }

    /**
     * @param array<string, mixed> $activity
     */
    private function hasSummaryPolyline(array $activity): bool
    {
        $map = $activity['map'] ?? null;

        return is_array($map)
            && isset($map['summary_polyline'])
            && is_string($map['summary_polyline'])
            && trim($map['summary_polyline']) !== '';
    }
}
