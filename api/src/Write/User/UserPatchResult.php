<?php

namespace App\Write\User;

use App\Entity\User;

final readonly class UserPatchResult
{
    public function __construct(
        public User $user,
        public bool $changed,
    ) {
    }
}
