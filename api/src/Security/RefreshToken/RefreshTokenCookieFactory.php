<?php

namespace App\Security\RefreshToken;

use Symfony\Component\HttpFoundation\Cookie;

final readonly class RefreshTokenCookieFactory
{
    public const string COOKIE_NAME = 'trainingpulse_refresh_token';

    public function create(string $plainToken, \DateTimeImmutable $expiresAt): Cookie
    {
        return Cookie::create(
            name: self::COOKIE_NAME,
            value: $plainToken,
            expire: $expiresAt,
            path: '/api/v1/auth',
            domain: null,
            secure: true,
            httpOnly: true,
            raw: false,
            sameSite: Cookie::SAMESITE_LAX,
        );
    }

    public function clear(): Cookie
    {
        return Cookie::create(
            name: self::COOKIE_NAME,
            value: '',
            expire: new \DateTimeImmutable('-1 hour'),
            path: '/api/v1/auth',
            domain: null,
            secure: true,
            httpOnly: true,
            raw: false,
            sameSite: Cookie::SAMESITE_LAX,
        );
    }
}
