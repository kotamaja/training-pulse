<?php

namespace App\Dto\Auth;

final class RefreshTokenResponseDto
{
    public function __construct(public string  $token,
                                public ?string $refreshToken = null)
    {
    }
}
