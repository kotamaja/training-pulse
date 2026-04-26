<?php

namespace App\Mapper;

use Doctrine\Common\Util\ClassUtils;
use Psr\Container\ContainerInterface;

final readonly class MapperRegistry implements MapperRegistryInterface
{
    /**
     * @param array<string, string> $mapperIdsByKey key = "Source->Target", value = service id
     */
    public function __construct(
        private array $mapperIdsByKey,
        private ContainerInterface $mapperLocator,
    ) {
    }

    public function map(object $source, string $targetClass): object
    {
        $sourceClass = ClassUtils::getClass($source);
        $key = $sourceClass . '->' . $targetClass;

        $serviceId = $this->mapperIdsByKey[$key] ?? null;

        if ($serviceId === null) {
            throw new \RuntimeException(sprintf(
                'No mapper registered for "%s" -> "%s".',
                $sourceClass,
                $targetClass,
            ));
        }

        /** @var CustomMapperInterface $mapper */
        $mapper = $this->mapperLocator->get($serviceId);

        return $mapper->map($source);
    }
}
