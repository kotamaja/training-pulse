<?php

namespace App\Security\RefreshToken;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RefreshTokenService
{
    private const REFRESH_TOKEN_BYTES = 64;

    public function __construct(
        private RefreshTokenRepository $refreshTokenRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function issue(User $user, RefreshTokenMode $mode): IssuedRefreshToken
    {
        $plainToken = $this->generatePlainToken();
        $tokenHash = $this->hashPlainToken($plainToken);

        $refreshToken = new RefreshToken(
            user: $user,
            tokenHash: $tokenHash,
            mode: $mode,
            expiresAt: new \DateTimeImmutable('+30 days'),
        );

        $this->entityManager->persist($refreshToken);

        return new IssuedRefreshToken(
            plainToken: $plainToken,
            entity: $refreshToken,
        );
    }

    public function consume(string $plainToken, RefreshTokenMode $transportMode): RefreshToken
    {
        $tokenHash = $this->hashPlainToken($plainToken);

        $refreshToken = $this->refreshTokenRepository->findOneByTokenHash($tokenHash);

        if (!$refreshToken instanceof RefreshToken) {
            throw new InvalidRefreshTokenException('Invalid refresh token.');
        }

        if ($refreshToken->getMode() !== $transportMode) {
            throw new InvalidRefreshTokenException('Invalid refresh token transport.');
        }

        if (!$refreshToken->isUsable()) {
            throw new InvalidRefreshTokenException('Refresh token is expired or revoked.');
        }

        $refreshToken->markUsed();

        return $refreshToken;
    }

    public function rotate(RefreshToken $refreshToken): IssuedRefreshToken
    {
        $refreshToken->revoke();

        return $this->issue(
            user: $refreshToken->getUser(),
            mode: $refreshToken->getMode(),
        );
    }

    public function revoke(RefreshToken $refreshToken): void
    {
        $refreshToken->revoke();
    }


    public function revokePlainToken(string $plainToken): void
    {
        $tokenHash = $this->hashPlainToken($plainToken);

        $refreshToken = $this->refreshTokenRepository->findOneByTokenHash($tokenHash);

        if (!$refreshToken instanceof RefreshToken) {
            return;
        }

        $refreshToken->revoke();
    }

    public function hashPlainToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    private function generatePlainToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(self::REFRESH_TOKEN_BYTES)), '+/', '-_'), '=');
    }
}
