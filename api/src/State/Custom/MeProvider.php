<?php

namespace App\State\Custom;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Me\MeDetailDto;
use App\Entity\User;
use App\Mapper\MapperRegistry;
use Symfony\Bundle\SecurityBundle\Security;

class MeProvider implements ProviderInterface
{


    public function __construct(private Security $security,
                                private readonly MapperRegistry    $mapperRegistry,)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->security->getUser();
        assert($user instanceof User);
        return $this->mapperRegistry->map($user, MeDetailDto::class);
    }
}
