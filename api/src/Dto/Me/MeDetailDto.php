<?php

namespace App\Dto\Me;

use App\Dto\Athlete\AthleteDetailDto;
use App\Dto\User\UserDetailDto;

final class MeDetailDto
{
    public UserDetailDto $user;

    public AthleteDetailDto $athlete;
}
