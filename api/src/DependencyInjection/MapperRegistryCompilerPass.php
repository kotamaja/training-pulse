<?php

namespace App\DependencyInjection;

use App\Mapper\MapperRegistry;
use App\Mapper\Maps;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class MapperRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(MapperRegistry::class)) {
            return;
        }

        $mapperIdsByKey = [];
        $locatorMap = [];

        foreach ($container->findTaggedServiceIds('app.mapper') as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);
            $class = $definition->getClass();

            if ($class === null) {
                continue;
            }

            if (!class_exists($class)) {
                throw new \LogicException(sprintf(
                    'Mapper service "%s" references class "%s", but this class does not exist.',
                    $serviceId,
                    $class,
                ));
            }

            $reflectionClass = new ReflectionClass($class);
            $attributes = $reflectionClass->getAttributes(Maps::class);

            if (count($attributes) !== 1) {
                throw new \LogicException(sprintf(
                    'Mapper "%s" must declare exactly one #[%s] attribute.',
                    $class,
                    Maps::class,
                ));
            }

            /** @var Maps $maps */
            $maps = $attributes[0]->newInstance();

            $key = $maps->source . '->' . $maps->target;

            if (isset($mapperIdsByKey[$key])) {
                throw new \LogicException(sprintf(
                    'Duplicate mapper registered for "%s" -> "%s".',
                    $maps->source,
                    $maps->target,
                ));
            }

            $mapperIdsByKey[$key] = $serviceId;
            $locatorMap[$serviceId] = new Reference($serviceId);
        }

        $locatorReference = ServiceLocatorTagPass::register($container, $locatorMap);

        $container
            ->getDefinition(MapperRegistry::class)
            ->setArgument('$mapperIdsByKey', $mapperIdsByKey)
            ->setArgument('$mapperLocator', $locatorReference);
    }
}
