<?php

namespace ApiClientBundle;

use ApiClientBundle\DependencyInjection\CompilerPass\ServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApiClientBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ServiceCompilerPass());
        parent::build($container);
    }
}
