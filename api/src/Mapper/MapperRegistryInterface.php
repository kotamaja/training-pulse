<?php

namespace App\Mapper;

interface MapperRegistryInterface
{
    public function map(object $source, string $targetClass): object;
}
