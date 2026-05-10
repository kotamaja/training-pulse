<?php

namespace App\Security\RefreshToken;

use App\Entity\RefreshToken;

final readonly class IssuedRefreshToken
{
    public function __construct(
        public string $plainToken,
        public RefreshToken $entity,
    ) {
    }
}
