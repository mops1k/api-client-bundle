# Plugins

## Library has 3 ways to register plugins:

1. Global plugins:
To add global plugins you have to register service with tag `api.http_client.plugin`

2. Service and Query plugins:
To add service and query plugins you have to implement `getPlugins` method from interfaces and return array of Plugins.
Example:
```php
<?php
namespace App\Remote\Api;

use ApiClientBundle\Client\AbstractQuery;use Http\Client\Common\Plugin\AuthenticationPlugin;use Http\Client\Common\Plugin\RetryPlugin;

class MyQuery extends AbstractQuery
{
    protected ?string $path = '/some-path'
    protected string $format = 'json'; // Any format supported by symfony/serializer
    protected string $service = MyService::class;
    protected string $response = MyResponse::class;
    protected array $plugins = [new RetryPlugin(['retries' => 3])];
}
```

## Plugin priority
Plugins from several places will overwrite in this ordering:
`Global plugins -> Service plugins -> Query plugins`
Scopes:
| Plugin | Scope | Note |
| :----- | :-----: | :---- |
| Global plugins | Global | Uses in all services and queries |
| Service plugins | Service | Uses in concrete service and his queries |
| Query plugins | Query | Uses in concrete query |
