<?php

namespace App\Mapper;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.mapper')]
interface CustomMapperInterface
{
    public function map(object $source): object;
}
