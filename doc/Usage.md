# Usage

## Add api service
### Create class with service configuration

```php
<?php
namespace App\Remote\Api;

use ApiClientBundle\Client\AbstractService;

class MyService extends AbstractService
{
    protected string $scheme = 'http';
    protected string $host = 'some-host.ru'
}
```

### Create class with query configuration

```php
<?php
namespace App\Remote\Api;

use ApiClientBundle\Client\AbstractQuery;use ApiClientBundle\Enum\HttpMethodEnum;

class MyQuery extends AbstractQuery
{
    protected ?string $path = '/some-path'
    protected HttpMethodEnum $method = HttpMethodEnum::POST;
    protected string $format = 'json'; // Any format supported by symfony/serializer
    protected string $service = MyService::class;
    protected string $response = MyResponse::class;
    
    protected ?array $files = ['file' => '/some/file/path/to/image.png']; // sending file with multipart/form-data
    
    /**
     * @param array $query
     */
    public  function setQuery(array $query):void {
       $this->query = $query;
    }
}
```

### Create response class (DTO)

```php
<?php
namespace App\Remote\Api;

use ApiClientBundle\Client\ResponseInterface;

class MyResponse implements ResponseInterface
{
    public function __construct(
        public readonly string $status
    ) {}
}
```

### Make request in your code (example: Controller)

```php
<?php

namespace App\Controller;

use ApiClientBundle\HTTP\HttpClient;
use App\Remote\Api\MyQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class Controller extends AbstractController
{
    public function __invoke(HttpClient $httpClient)
    {
        $query = new MyQuery();
        $query->setQuery(['example' => 'simple'])
        $response = $httpClient->request($query);
        
        return new Response($response->status);
    }
}
```


### Exceptions
Library may throw 3 types of exceptions:
1. `\ApiClientBundle\HTTP\HttpClientException` - throw when some client problems occurs (unknown host, etc.)
2. `\ApiClientBundle\Exception\HttpRequestException` - throws when response status code >= 400 and <= 499
3. `\ApiClientBundle\Exception\ServerErrorException` - throws when response status code >= 500
