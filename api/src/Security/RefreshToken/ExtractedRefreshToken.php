<?php

namespace App\Security\RefreshToken;

final readonly class ExtractedRefreshToken
{
    public function __construct(public string           $plainToken,
                                public RefreshTokenMode $transportMode)
    {
    }
}
