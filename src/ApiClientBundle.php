<?php

namespace ApiClientBundle;

use ApiClientBundle\DependencyInjection\CompilerPass\ClientConfigurationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApiClientBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ClientConfigurationCompilerPass());
    }
}
