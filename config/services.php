<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ApiClientBundle\HTTP\HttpClient;
use Http\Client\Common\Plugin\ErrorPlugin;
use Symfony\Component\Serializer\SerializerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ErrorPlugin::class)
             ->arg('$config', [
                 'only_server_exception' => false,
             ])
             ->tag('api.http_client.plugin')
    ;

    $services->set(HttpClient::class)
             ->arg('$serializer', service(SerializerInterface::class))
             ->arg('$plugins', tagged_iterator('api.http_client.plugin'))
             ->public()
    ;
};
