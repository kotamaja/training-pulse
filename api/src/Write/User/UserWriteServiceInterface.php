<?php

namespace App\Write\User;

use App\Dto\User\UserCreateDto;
use App\Dto\User\UserPatchDto;
use App\Entity\User;

interface UserWriteServiceInterface
{
    public function create(UserCreateDto $input, ?User $actor = null): User;

    public function patch(UserPatchDto $input, User $user, ?User $actor = null): UserPatchResult;

    public function delete(User $user, ?User $actor = null): void;
}
