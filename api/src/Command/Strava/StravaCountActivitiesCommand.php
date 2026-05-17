<?php

namespace App\Command\Strava;

use App\Integration\Strava\Sync\StravaActivityCountService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:strava:count-activities',
    description: 'Count Strava activity summaries without fetching streams.',
)]
final class StravaCountActivitiesCommand extends Command
{
    public function __construct(
        private readonly StravaCommandAccountResolver $accountResolver,
        private readonly StravaActivityCountService $countService,
        #[Autowire(param: 'strava.activity_summary_per_page')]private readonly int $defaultPerPage,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'TrainingPulse user email.',
            )
            ->addOption(
                'after',
                null,
                InputOption::VALUE_REQUIRED,
                'Only count activities after this date, format YYYY-MM-DD.',
            )
            ->addOption(
                'before',
                null,
                InputOption::VALUE_REQUIRED,
                'Only count activities before this date, format YYYY-MM-DD.',
            )
            ->addOption(
                'per-page',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of activity summaries per Strava page.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $this->getRequiredStringOption($input, 'email');

        $after = $this->parseDateOption($input->getOption('after'), 'after');
        $before = $this->parseDateOption($input->getOption('before'), 'before');

        $perPageOption = $input->getOption('per-page');

        $perPage = $perPageOption !== null && $perPageOption !== ''
            ? (int) $perPageOption
            : $this->defaultPerPage;

        $perPage = min(200, max(1, $perPage));

        $account = $this->accountResolver->resolveStravaAccountByEmail($email);
        $athlete = $account->getAthlete();
        $user = $athlete->getUser();

        $io->title('Strava activity count');

        $io->definitionList(
            ['TrainingPulse user' => $user->getEmail()],
            ['TrainingPulse athlete' => $athlete->getDisplayName()],
            ['Strava account id' => $account->getProviderAccountId()],
            ['After' => $after?->format('Y-m-d') ?? 'n/a'],
            ['Before' => $before?->format('Y-m-d') ?? 'n/a'],
            ['Per page' => (string) $perPage],
        );

        $report = $this->countService->countActivities(
            account: $account,
            perPage: $perPage,
            after: $after,
            before: $before,
        );

        $io->success('Strava activity count completed.');

        $io->definitionList(
            ['Activities found' => (string) $report->activitiesFound],
            ['Summary pages fetched' => (string) $report->pagesFetched],
            ['Summary requests used' => (string) $report->pagesFetched],
            ['Estimated stream requests' => (string) $report->estimatedStreamRequests()],
            ['Estimated full import requests' => (string) $report->estimatedFullImportRequests()],
        );

        return Command::SUCCESS;
    }

    private function getRequiredStringOption(InputInterface $input, string $name): string
    {
        $value = $input->getOption($name);

        if (!is_string($value) || trim($value) === '') {
            throw new \InvalidArgumentException(sprintf(
                'Missing required option --%s.',
                $name,
            ));
        }

        return trim($value);
    }

    private function parseDateOption(mixed $value, string $name): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Option "%s" must be a string.',
                $name,
            ));
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
}
