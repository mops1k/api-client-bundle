<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ApiClientBundle\HTTP\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(HttpClient::class)
             ->arg('$serializer', service(SerializerInterface::class));
};
