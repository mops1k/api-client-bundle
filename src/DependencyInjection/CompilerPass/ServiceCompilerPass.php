<?php

namespace ApiClientBundle\DependencyInjection\CompilerPass;

use ApiClientBundle\Client\ServiceInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if (null === $definition->getClass() || !class_exists($definition->getClass())) {
                continue;
            }

            try {
                $class = new \ReflectionClass($definition->getClass());
                if (!$class->implementsInterface(ServiceInterface::class)) {
                    continue;
                }

                $definition->setPublic(true);
                $container->setDefinition($id, $definition);
            } catch (\Throwable) {
            }
        }
    }
}
