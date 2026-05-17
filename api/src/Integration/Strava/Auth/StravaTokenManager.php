<?php

namespace App\Integration\Strava\Auth;

use App\Entity\AthleteExternalAccount;
use App\Enum\ActivitySource;
use App\Integration\Strava\Api\StravaTokenRefreshClient;
use App\Write\Exception\BusinessRuleViolationException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class StravaTokenManager
{
    public function __construct(
        private StravaTokenRefreshClient $refreshClient,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getValidAccessToken(AthleteExternalAccount $account): string
    {
        if ($account->getProvider() !== ActivitySource::Strava) {
            throw new BusinessRuleViolationException(
                message: 'External account is not a Strava account.',
                field: 'provider',
            );
        }

        if (!$this->shouldRefresh($account)) {
            return $account->requireAccessToken();
        }

        $tokenData = $this->refreshClient->refreshAccessToken(
            refreshToken: $account->requireRefreshToken(),
        );

        $accessToken = $this->requireString($tokenData, 'access_token');
        $refreshToken = $this->requireString($tokenData, 'refresh_token');
        $expiresAt = $this->requireInt($tokenData, 'expires_at');

        $account->setAccessToken($accessToken);
        $account->setRefreshToken($refreshToken);
        $account->setExpiresAt(
            (new \DateTimeImmutable('@' . $expiresAt))->setTimezone(new \DateTimeZone('UTC')),
        );
        $account->clearLastError();

        $this->entityManager->flush();

        return $accessToken;
    }

    private function shouldRefresh(AthleteExternalAccount $account): bool
    {
        $expiresAt = $account->getExpiresAt();

        if ($expiresAt === null) {
            return true;
        }

        $refreshBefore = new \DateTimeImmutable('+10 minutes');

        return $expiresAt <= $refreshBefore;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireString(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        if (!is_string($value) || trim($value) === '') {
            throw new \RuntimeException(sprintf('Missing or invalid Strava token value "%s".', $key));
        }

        return trim($value);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireInt(array $data, string $key): int
    {
        $value = $data[$key] ?? null;

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        throw new \RuntimeException(sprintf('Missing or invalid Strava token value "%s".', $key));
    }
}
