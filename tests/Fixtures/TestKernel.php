<?php

declare(strict_types=1);

namespace ApiClientBundle\Tests\Fixtures;

use ApiClientBundle\ApiClientBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new ApiClientBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->prependExtensionConfig('framework', [
                'test' => true,
                'property_access' => [
                    'enabled' => true,
                ],
                'property_info' => [
                    'enabled' => true,
                ],
            ]);
        });
    }
}
