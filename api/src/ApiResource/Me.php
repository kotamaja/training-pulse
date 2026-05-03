<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\Me\MeDetailDto;
use App\Security\Role;
use App\State\Custom\MeProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/me',
            normalizationContext: [
                'skip_null_values' => false,
            ],
            security: "is_granted('" . Role::ROLE_USER . "')",
            output: MeDetailDto::class,
            provider: MeProvider::class,
        )
    ],
    routePrefix: '/v1',
)]
final class Me
{

}
