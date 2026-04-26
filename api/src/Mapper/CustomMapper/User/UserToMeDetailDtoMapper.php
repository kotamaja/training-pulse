<?php

namespace App\Mapper\CustomMapper\User;

use App\Dto\Athlete\AthleteDetailDto;
use App\Dto\Me\MeDetailDto;
use App\Dto\User\UserDetailDto;
use App\Entity\Athlete;
use App\Entity\User;
use App\Mapper\CustomMapperInterface;
use App\Mapper\MapperRegistry;
use App\Mapper\Maps;

#[Maps(source: User::class, target: MeDetailDto::class)]
class UserToMeDetailDtoMapper implements CustomMapperInterface
{


    public function __construct(private readonly MapperRegistry $mapperRegistry)
    {
    }


    public function map(object $source): object
    {
        if (!$source instanceof User) {
            throw new \InvalidArgumentException('Expected Athlete.');
        }

        $dto = new MeDetailDto();

        /** @var UserDetailDto $userDetailDto */
        $userDetailDto = $this->mapperRegistry->map($source, UserDetailDto::class);

        $athleteDetailDto = null;
        $athlete = $source->getAthlete();
            /** @var AthleteDetailDto $athleteDetailDto */
            $athleteDetailDto = $this->mapperRegistry->map($athlete, AthleteDetailDto::class);

        $dto->user = $userDetailDto;
        $dto->athlete = $athleteDetailDto;


        return $dto;

    }
}
