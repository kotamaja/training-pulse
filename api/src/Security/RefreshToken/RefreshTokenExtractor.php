<?php

namespace App\Security\RefreshToken;

use Symfony\Component\HttpFoundation\Request;

final readonly class RefreshTokenExtractor
{
    public function extract(Request $request): ?ExtractedRefreshToken
    {
        $cookieToken = $request->cookies->get(RefreshTokenCookieFactory::COOKIE_NAME);

        if (is_string($cookieToken) && $cookieToken !== '') {
            return new ExtractedRefreshToken(
                plainToken: $cookieToken,
                transportMode: RefreshTokenMode::Web,
            );
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return null;
        }

        $bodyToken = $data['refreshToken'] ?? null;

        if (!is_string($bodyToken) || $bodyToken === '') {
            return null;
        }

        return new ExtractedRefreshToken(
            plainToken: $bodyToken,
            transportMode: RefreshTokenMode::Token,
        );
    }

    public function extractPlainToken(Request $request): ?string
    {
        return $this->extract($request)?->plainToken;
    }
}
