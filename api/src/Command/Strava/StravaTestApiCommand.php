<?php

namespace App\Command\Strava;

use App\Entity\User;
use App\Enum\ActivitySource;
use App\Integration\Strava\StravaApiClient;
use App\Integration\Strava\StravaTokenManager;
use App\Repository\AthleteExternalAccountRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:strava:test-api',
    description: 'Test the Strava API connection using the connected dev user account.',
)]
final class StravaTestApiCommand extends Command
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = $this->findDevUser();
        $athlete = $user->requireAthlete();

        $externalAccount = $this->externalAccountRepository->findOneForAthleteAndProvider(
            athlete: $athlete,
            provider: ActivitySource::Strava,
        );

        if ($externalAccount === null) {
            $io->error(sprintf(
                'No Strava external account found for athlete "%s". Run app:strava:connect-url and app:strava:exchange-code first.',
                $athlete->getDisplayName(),
            ));

            return Command::FAILURE;
        }

        $io->section('TrainingPulse account');
        $io->definitionList(
            ['User' => $user->getEmail()],
            ['Athlete' => $athlete->getDisplayName()],
            ['Provider' => $externalAccount->getProvider()->value],
            ['Provider account id' => $externalAccount->getProviderAccountId()],
            ['Token expires at' => $externalAccount->getExpiresAt()?->format('Y-m-d\TH:i:s\Z') ?? 'n/a'],
        );

        $accessToken = $this->tokenManager->getValidAccessToken($externalAccount);

        $io->section('Calling Strava API');
        $stravaAthlete = $this->stravaApiClient->getAthlete($accessToken);

        $io->success('Strava API call succeeded.');

        $io->definitionList(
            ['Strava id' => (string)($stravaAthlete['id'] ?? 'n/a')],
            ['Username' => (string)($stravaAthlete['username'] ?? 'n/a')],
            ['Firstname' => (string)($stravaAthlete['firstname'] ?? 'n/a')],
            ['Lastname' => (string)($stravaAthlete['lastname'] ?? 'n/a')],
            ['City' => (string)($stravaAthlete['city'] ?? 'n/a')],
            ['Country' => (string)($stravaAthlete['country'] ?? 'n/a')],
        );

        return Command::SUCCESS;
    }

    private function findDevUser(): User
    {
        $user = $this->userRepository->findOneBy([
            'email' => self::DEV_USER_EMAIL,
        ]);

        if (!$user instanceof User) {
            throw new \RuntimeException(sprintf(
                'Dev user "%s" was not found.',
                self::DEV_USER_EMAIL,
            ));
        }

        return $user;
    }
}
