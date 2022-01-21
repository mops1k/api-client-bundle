<?php

namespace ApiClientBundle\DependencyInjection\CompilerPass;

use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClientConfigurationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if (!$definition->getClass() || !class_exists($definition->getClass())) {
                continue;
            }

            try {
                $class = new \ReflectionClass($definition->getClass());
                if (!$class->implementsInterface(ClientConfigurationInterface::class)) {
                    continue;
                }

                $definition->addTag('api.client');
                $container->setDefinition($id, $definition);
            } catch (\Throwable) {
            }
        }
    }
}
