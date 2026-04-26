<?php

namespace App\DataFixtures;

use App\Dto\User\UserCreateDto;
use App\Security\Role;
use App\Write\User\UserWriteServiceInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{


    public function __construct(private readonly UserWriteServiceInterface $userWriteService)
    {
    }

    public function load(ObjectManager $manager): void
    {


        $meDto = new UserCreateDto();
        $meDto->email = "dev@trainingpulse.local";
        $meDto->username = "dev";
        $meDto->enabled = true;
        $meDto->roles = [ Role::ROLE_USER,  Role::ROLE_ADMIN];

        $me = $this->userWriteService->create($meDto);
        $manager->flush();

        $this->addReference('me-dev', $me);

    }
}
