<?php

namespace App\Mapper\CustomMapper\User;

use App\Dto\User\UserDetailDto;
use App\Entity\User;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: User::class, target: UserDetailDto::class)]
class UserToUserDetailDtoMapper implements CustomMapperInterface
{

    public function map(object $source): object
    {
        if (!$source instanceof User) {
            throw new \InvalidArgumentException('Expected User.');
        }

        $dto = new UserDetailDto();
        $dto->id = $source->getPublicId();
        $dto->email = $source->getEmail();
        $dto->username = $source->getUsername();
        $dto->roles = $source->getRoles();
        $dto->enabled = $source->isEnabled();

        $dto->createdAt = $source->getCreatedAt()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');

        if ($source->getUpdatedAt()) {
            $dto->updatedAt = $source->getUpdatedAt()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
        }


        return $dto;

    }
}
