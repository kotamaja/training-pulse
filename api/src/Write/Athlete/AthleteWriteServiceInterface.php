<?php

namespace App\Write\Athlete;

use App\Dto\Athlete\AthleteCreateDto;
use App\Dto\Athlete\AthletePatchDto;
use App\Entity\Athlete;
use App\Entity\User;

interface AthleteWriteServiceInterface
{
    public function create(AthleteCreateDto $input, User $user, ?User $actor = null): Athlete;

    public function patch(AthletePatchDto $input, Athlete $athlete, ?User $actor = null): AthletePatchResult;

    public function delete(Athlete $athlete, ?User $actor = null): void;
}
