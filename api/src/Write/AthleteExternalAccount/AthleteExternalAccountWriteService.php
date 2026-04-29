<?php

namespace App\Write\AthleteExternalAccount;

use App\Entity\Athlete;
use App\Entity\AthleteExternalAccount;
use App\Enum\ActivitySource;
use App\Enum\ExternalAccountStatus;
use App\Repository\AthleteExternalAccountRepository;
use App\Write\Exception\BusinessRuleViolationException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AthleteExternalAccountWriteService implements AthleteExternalAccountWriteServiceInterface
{
    public function __construct(
        private EntityManagerInterface           $entityManager,
        private AthleteExternalAccountRepository $athleteExternalAccountRepository,
    )
    {
    }

    public function connectOrUpdate(Athlete            $athlete,
                                    ActivitySource     $provider,
                                    string             $providerAccountId,
                                    string             $accessToken,
                                    string             $refreshToken,
                                    \DateTimeImmutable $expiresAt,
                                    array              $scopes = [],
                                    ?string            $displayName = null,): AthleteExternalAccount
    {
        $providerAccountId = trim($providerAccountId);
        $accessToken = trim($accessToken);
        $refreshToken = trim($refreshToken);
        $displayName = $this->normalizeNullableString($displayName);

        if ($providerAccountId === '') {
            throw new BusinessRuleViolationException(
                message: 'Provider account id cannot be empty.',
                field: 'providerAccountId',
            );
        }

        if ($accessToken === '') {
            throw new BusinessRuleViolationException(
                message: 'Access token cannot be empty.',
                field: 'accessToken',
            );
        }

        if ($refreshToken === '') {
            throw new BusinessRuleViolationException(
                message: 'Refresh token cannot be empty.',
                field: 'refreshToken',
            );
        }

        $scopes = $this->normalizeScopes($scopes);

        $account = $this->athleteExternalAccountRepository->findOneBy([
            'athlete' => $athlete,
            'provider' => $provider,
            'providerAccountId' => $providerAccountId,
        ]);

        if ($account === null) {
            $account = new AthleteExternalAccount(
                athlete: $athlete,
                provider: $provider,
                providerAccountId: $providerAccountId,
            );

            $this->entityManager->persist($account);
        }

        $account->setDisplayName($displayName);
        $account->setAccessToken($accessToken);
        $account->setRefreshToken($refreshToken);
        $account->setExpiresAt($expiresAt);
        $account->setScopes($scopes);
        $account->setStatus(ExternalAccountStatus::Active);
        $account->clearLastError();

        return $account;
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param array<mixed> $scopes
     *
     * @return list<string>
     */
    private function normalizeScopes(array $scopes): array
    {
        $normalizedScopes = [];

        foreach ($scopes as $scope) {
            if (!is_string($scope)) {
                continue;
            }

            $scope = trim($scope);

            if ($scope !== '') {
                $normalizedScopes[] = $scope;
            }
        }

        $normalizedScopes = array_values(array_unique($normalizedScopes));
        sort($normalizedScopes);

        return $normalizedScopes;
    }
}
