<?php

namespace App\Command\Strava;

use App\Enum\ActivitySource;
use App\Integration\Strava\StravaTokenExchangeClient;
use App\Write\AthleteExternalAccount\AthleteExternalAccountWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:strava:exchange-code',
    description: 'Exchange a Strava OAuth authorization code for tokens and store the connected account.',
)]
final class StravaExchangeCodeCommand extends Command
{

    public function __construct(
        private readonly StravaTokenExchangeClient                   $tokenExchangeClient,
        private readonly AthleteExternalAccountWriteServiceInterface $externalAccountWriteService,
        private readonly StravaCommandAccountResolver                $accountResolver,
        private readonly EntityManagerInterface                      $entityManager,
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
            ->addArgument(
                'code',
                InputArgument::REQUIRED,
                'The OAuth code returned by Strava.',
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

        /** @var string $code */
        $code = $input->getArgument('code');

        $user = $this->accountResolver->resolveUserByEmail($email);
        $athlete = $user->requireAthlete();

        $tokenData = $this->tokenExchangeClient->exchangeCode($code);

        $providerAccountId = $this->requireStringValue($tokenData, ['athlete', 'id'], 'athlete.id');
        $accessToken = $this->requireStringValue($tokenData, ['access_token'], 'access_token');
        $refreshToken = $this->requireStringValue($tokenData, ['refresh_token'], 'refresh_token');
        $expiresAtTimestamp = $this->requireIntValue($tokenData, ['expires_at'], 'expires_at');

        $scopes = $this->extractScopes($tokenData);
        $displayName = $this->buildStravaDisplayName($tokenData['athlete'] ?? []);

        $account = $this->externalAccountWriteService->connectOrUpdate(
            athlete: $athlete,
            provider: ActivitySource::Strava,
            providerAccountId: $providerAccountId,
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresAt: (new \DateTimeImmutable('@' . $expiresAtTimestamp))->setTimezone(new \DateTimeZone('UTC')),
            scopes: $scopes,
            displayName: $displayName,
        );

        $this->entityManager->flush();

        $io->success('Strava account connected successfully.');

        $io->definitionList(
            ['TrainingPulse user' => $user->getEmail()],
            ['TrainingPulse athlete' => $athlete->getDisplayName()],
            ['Provider' => $account->getProvider()->value],
            ['Provider account id' => $account->getProviderAccountId()],
            ['Display name' => $displayName ?? 'n/a'],
            ['Expires at' => $account->getExpiresAt()?->format('Y-m-d\TH:i:s\Z') ?? 'n/a'],
            ['Scopes' => $scopes !== [] ? implode(', ', $scopes) : 'n/a'],
            ['Access token' => $this->maskSecret($accessToken)],
            ['Refresh token' => $this->maskSecret($refreshToken)],
        );

        $io->note('Tokens are stored in AthleteExternalAccount. They are masked here on purpose.');

        return Command::SUCCESS;
    }


    /**
     * @param array<string, mixed> $data
     * @param list<string> $path
     */
    private function requireStringValue(array $data, array $path, string $label): string
    {
        $value = $this->readPath($data, $path);

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        if (!is_string($value) || trim($value) === '') {
            throw new \RuntimeException(sprintf('Missing or invalid Strava response value "%s".', $label));
        }

        return trim($value);
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $path
     */
    private function requireIntValue(array $data, array $path, string $label): int
    {
        $value = $this->readPath($data, $path);

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int)$value;
        }

        throw new \RuntimeException(sprintf('Missing or invalid Strava response value "%s".', $label));
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $path
     */
    private function readPath(array $data, array $path): mixed
    {
        $current = $data;

        foreach ($path as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * @param array<string, mixed> $tokenData
     *
     * @return list<string>
     */
    private function extractScopes(array $tokenData): array
    {
        $scope = $tokenData['scope'] ?? null;

        if (!is_string($scope) || trim($scope) === '') {
            return [];
        }

        $scopes = array_map('trim', explode(',', $scope));
        $scopes = array_filter($scopes, static fn(string $scope): bool => $scope !== '');
        $scopes = array_values(array_unique($scopes));
        sort($scopes);

        return $scopes;
    }

    /**
     * @param mixed $athleteData
     */
    private function buildStravaDisplayName(mixed $athleteData): ?string
    {
        if (!is_array($athleteData)) {
            return null;
        }

        $firstname = isset($athleteData['firstname']) && is_string($athleteData['firstname'])
            ? trim($athleteData['firstname'])
            : '';

        $lastname = isset($athleteData['lastname']) && is_string($athleteData['lastname'])
            ? trim($athleteData['lastname'])
            : '';

        $displayName = trim($firstname . ' ' . $lastname);

        if ($displayName !== '') {
            return $displayName;
        }

        return isset($athleteData['username']) && is_string($athleteData['username']) && trim($athleteData['username']) !== ''
            ? trim($athleteData['username'])
            : null;
    }

    private function maskSecret(string $value): string
    {
        if (strlen($value) <= 12) {
            return '********';
        }

        return substr($value, 0, 6) . '...' . substr($value, -6);
    }
}
