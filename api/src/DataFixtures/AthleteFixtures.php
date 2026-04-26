<?php

namespace App\DataFixtures;

use App\Dto\Athlete\AthleteCreateDto;
use App\Entity\User;
use App\Write\Athlete\AthleteWriteServiceInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AthleteFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(private readonly AthleteWriteServiceInterface $athleteWriteService)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $createDto = new AthleteCreateDto();
        $createDto->birthYear = 2000;
        $createDto->displayName = "Kotajama";
        $createDto->ftpWatts = 330;
        $createDto->weightKg = 80;
        $createDto->heightCm = 180;
        $createDto->maxHeartRate = 190;
        $createDto->restingHeartRate = 45;


        $me = $this->getReference("me-dev", User::class);


        $athlete = $this->athleteWriteService->create($createDto, $me);
        $manager->flush();

        $this->addReference('athlete', $athlete);
    }


    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
