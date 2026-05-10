<?php

namespace App\Command;

use App\Repository\RefreshTokenRepository;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:auth:purge-refresh-tokens',
    description: 'Purge old expired or revoked refresh tokens.',
)]
final class PurgeRefreshTokensCommand extends Command
{
    public function __construct(private readonly RefreshTokenRepository $refreshTokenRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'retention-days',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Number of days to keep expired or revoked refresh tokens.',
            default: '30',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $retentionDays = (int)$input->getOption('retention-days');

        if ($retentionDays < 0) {
            $io->error('The retention-days option must be greater than or equal to 0.');

            return Command::FAILURE;
        }

        $threshold = new DateTimeImmutable(sprintf('-%d days', $retentionDays));

        $deletedCount = $this->refreshTokenRepository->purgeExpiredOrRevokedBefore($threshold);

        $io->success(sprintf(
            'Purged %d refresh token(s) expired or revoked before %s.',
            $deletedCount,
            $threshold->format(\DateTimeInterface::ATOM),
        ));

        return Command::SUCCESS;
    }
}
