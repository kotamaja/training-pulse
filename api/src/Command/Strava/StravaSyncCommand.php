<?php

namespace App\Command\Strava;

use App\Integration\Strava\Sync\StravaActivitySyncReport;
use App\Integration\Strava\Sync\StravaActivitySyncService;
use App\Integration\Strava\Sync\StravaStreamSyncMode;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:strava:sync',
    description: 'Synchronize Strava activities for the connected dev user account.',
)]
final class StravaSyncCommand extends Command
{

    public function __construct(
        private readonly StravaCommandAccountResolver $accountResolver,
        private readonly StravaActivitySyncService    $syncService,
    )
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
                30,
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
            )
            ->addOption(
                'all-pages',
                null,
                InputOption::VALUE_NONE,
                'Synchronize all pages until Strava returns less than per-page activities.',
            )->addOption(
                'sleep-between-pages',
                null,
                InputOption::VALUE_REQUIRED,
                'Seconds to sleep between pages when using --all-pages.',
                0,
            )->addOption(
                'with-streams',
                null,
                InputOption::VALUE_REQUIRED,
                'Stream synchronization mode: no, missing, always.',
                StravaStreamSyncMode::Missing->value,
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

        $page = max(1, (int)$input->getOption('page'));
        $perPage = min(200, max(1, (int)$input->getOption('per-page')));

        $after = $this->parseDateOption($input->getOption('after'), 'after');
        $before = $this->parseDateOption($input->getOption('before'), 'before');

        $allPages = (bool)$input->getOption('all-pages');
        $sleepBetweenPages = max(0, (int)$input->getOption('sleep-between-pages'));

        $streamSyncMode = $this->parseStreamSyncMode((string)$input->getOption('with-streams'));

        $user = $this->accountResolver->resolveUserByEmail($email);
        $athlete = $user->requireAthlete();
        $account = $this->accountResolver->resolveStravaAccountByEmail($email);
        if ($account === null) {
            $io->error(sprintf(
                'No Strava external account found for athlete "%s".',
                $athlete->getDisplayName(),
            ));

            return Command::FAILURE;
        }

        $io->title('Strava activity synchronization');

        $io->definitionList(
            ['TrainingPulse user' => $user->getEmail()],
            ['TrainingPulse athlete' => $athlete->getDisplayName()],
            ['Strava account id' => $account->getProviderAccountId()],
            ['Page' => (string)$page],
            ['Per page' => (string)$perPage],
            ['After' => $after?->format('Y-m-d') ?? 'n/a'],
            ['Before' => $before?->format('Y-m-d') ?? 'n/a'],
        );

        if (!$allPages) {
            $report = $this->syncService->syncAccount(
                account: $account,
                page: $page,
                perPage: $perPage,
                after: $after,
                before: $before,
                streamSyncMode: $streamSyncMode,
            );
        } else {
            $report = new StravaActivitySyncReport();

            $currentPage = $page;

            while (true) {
                $io->section(sprintf('Synchronizing Strava page %d', $currentPage));

                $pageReport = $this->syncService->syncAccount(
                    account: $account,
                    page: $currentPage,
                    perPage: $perPage,
                    after: $after,
                    before: $before,
                );

                $report->merge($pageReport);

                $io->definitionList(
                    ['Page fetched' => (string)$pageReport->fetched],
                    ['Page created' => (string)$pageReport->created],
                    ['Page updated' => (string)$pageReport->updated],
                    ['Page unchanged' => (string)$pageReport->unchanged],
                    ['Page failed' => (string)$pageReport->failed],
                );

                if ($pageReport->fetched < $perPage) {
                    break;
                }

                $currentPage++;

                if ($sleepBetweenPages > 0) {
                    sleep($sleepBetweenPages);
                }
            }
        }

        $io->success('Strava synchronization completed.');

        $io->definitionList(
            ['Fetched' => (string)$report->fetched],
            ['Created' => (string)$report->created],
            ['Updated' => (string)$report->updated],
            ['Unchanged' => (string)$report->unchanged],
            ['Failed' => (string)$report->failed],
            ['All pages' => $allPages ? 'yes' : 'no'],
            ['Sleep between pages' => $allPages ? (string)$sleepBetweenPages . 's' : 'n/a'],
        );

        if ($report->errors !== []) {
            $io->section('Errors');

            foreach ($report->errors as $error) {
                $io->writeln('- ' . $error);
            }

            return Command::FAILURE;
        }

        return Command::SUCCESS;
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

    private function parseStreamSyncMode(mixed $value): StravaStreamSyncMode
    {
        if (!is_string($value) || trim($value) === '') {
            throw new \InvalidArgumentException('Option "--with-streams" must be a non-empty string.');
        }

        return match (strtolower(trim($value))) {
            'no', 'none', 'false', '0' => StravaStreamSyncMode::No,
            'missing' => StravaStreamSyncMode::Missing,
            'always', 'yes', 'true', '1' => StravaStreamSyncMode::Always,
            default => throw new \InvalidArgumentException(sprintf(
                'Invalid --with-streams value "%s". Expected one of: no, missing, always.',
                $value,
            )),
        };
    }
}
