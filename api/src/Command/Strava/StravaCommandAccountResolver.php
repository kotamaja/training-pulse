<?php

namespace App\Command\Strava;

use App\Entity\Athlete;
use App\Entity\AthleteExternalAccount;
use App\Entity\User;
use App\Enum\ActivitySource;
use App\Repository\AthleteExternalAccountRepository;
use App\Repository\UserRepository;

final readonly class StravaCommandAccountResolver
{
    public function __construct(
        private UserRepository $userRepository,
        private AthleteExternalAccountRepository $externalAccountRepository,
    ) {
    }

    public function resolveUserByEmail(string $email): User
    {
        $email = mb_strtolower(trim($email));

        if ($email === '') {
            throw new \InvalidArgumentException('Email cannot be empty.');
        }

        $user = $this->userRepository->findOneBy([
            'email' => $email,
        ]);

        if (!$user instanceof User) {
            throw new \RuntimeException(sprintf(
                'User "%s" was not found.',
                $email,
            ));
        }

        if (!$user->isEnabled()) {
            throw new \RuntimeException(sprintf(
                'User "%s" is disabled.',
                $email,
            ));
        }

        return $user;
    }

    public function resolveAthleteByEmail(string $email): Athlete
    {
        return $this->resolveUserByEmail($email)->requireAthlete();
    }

    public function resolveStravaAccountByEmail(string $email): AthleteExternalAccount
    {
        $athlete = $this->resolveAthleteByEmail($email);

        $account = $this->externalAccountRepository->findOneForAthleteAndProvider(
            athlete: $athlete,
            provider: ActivitySource::Strava,
        );

        if (!$account instanceof AthleteExternalAccount) {
            throw new \RuntimeException(sprintf(
                'No Strava external account found for athlete "%s" / user "%s".',
                $athlete->getDisplayName(),
                $email,
            ));
        }

        return $account;
    }
}
