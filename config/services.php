<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ApiClientBundle\Builder\RequestUriBuilder;
use ApiClientBundle\HTTP\Context\ContextStorage;
use ApiClientBundle\HTTP\Context\ContextStorageInterface;
use ApiClientBundle\HTTP\HttpClient;
use ApiClientBundle\HTTP\HttpClientInterface;
use ApiClientBundle\Serializer\ListResponseDenormalizer;
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

    $services->set(ContextStorageInterface::class)
        ->class(ContextStorage::class)
    ;

    $services->set(HttpClientInterface::class)
             ->class(HttpClient::class)
             ->arg('$serializer', service(SerializerInterface::class))
             ->arg('$container', service('service_container'))
             ->arg('$contextStorage', service(ContextStorageInterface::class))
             ->arg('$plugins', tagged_iterator('api.http_client.plugin'))
             ->public()
    ;

    $services->set(ListResponseDenormalizer::class)
             ->tag('serializer.normalizer');
};
