<?php

namespace App\Dto\Me;

use App\Dto\Athlete\AthleteDetailDto;
use App\Dto\User\UserDetailDto;
use App\Entity\User;
use App\Mapper\MapperRegistry;

final readonly class MeDetailDtoFactory
{
    public function __construct(private MapperRegistry $mapperRegistry)
    {
    }

    public function fromUser(User $user): MeDetailDto
    {
        $athlete = $user->requireAthlete();

        /** @var UserDetailDto $userDto */
        $userDto = $this->mapperRegistry->map($user, UserDetailDto::class);

        /** @var AthleteDetailDto $athleteDto */
        $athleteDto = $this->mapperRegistry->map($athlete, AthleteDetailDto::class);

        $dto = new MeDetailDto();
        $dto->user= $userDto;
        $dto->athlete = $athleteDto;

        return $dto;
    }
}
