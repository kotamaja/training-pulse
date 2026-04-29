<?php

namespace App\Command\Strava;

use App\Integration\Strava\StravaOAuthUrlGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:strava:connect-url',
    description: 'Generate the Strava OAuth authorization URL.',
)]
final class StravaConnectUrlCommand extends Command
{
    public function __construct(
        private readonly StravaOAuthUrlGenerator $urlGenerator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $url = $this->urlGenerator->generateAuthorizeUrl();

        $io->title('Strava authorization URL');

        $io->writeln('Open this URL in your browser:');
        $io->newLine();
        $io->writeln($url);
        $io->newLine();

        $io->note([
            'After accepting the authorization on Strava, you will be redirected to your configured redirect URI.',
            'Copy the "code" query parameter from the redirected URL.',
            'Then run: php bin/console app:strava:exchange-code <code>',
        ]);

        return Command::SUCCESS;
    }
}
