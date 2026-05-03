<?php

namespace App\State\Custom;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Me\MeDetailDtoFactory;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class MeProvider implements ProviderInterface
{


    public function __construct(private Security           $security,
                                private MeDetailDtoFactory $factory)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new \LogicException('Authenticated user must be an App\Entity\User.');
        }

        return $this->factory->fromUser($user);
    }
}
