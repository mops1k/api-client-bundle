# ApiClientBundle
Symfony библиотека, позволяющая выполнять запросы к сторонним REST-API ресурсам посредством создания конфигурации и
отдельно предварительных конфигураций для различных эндпоинтов. Позволяет выполнив запрос получить десериализованный в
объект ответ от апи.

## Установка
```bash
composer require mops1k/api-client-bundle
```

## Использование

### Команды make
Библиотека поддерживает генерацию базовых классов с помощью использования `symfony/maker-bundle`.

На данный момент в проекте 2 команды генерации:
1. `bin/console make:api:client` - эта команда создаст класс конфигурации клиента, во время выполнения команды
будут заданы вопросы, на которые необходимо ответить для генерации класса конфигурации клиента.
2. `bin/console make:api:query` - эту команду надо выполнять только после того, как у вас уже есть хотя бы 1
конфигурация клиента. Она выполняет генерацию Query, Response и ErrorResponse(если необходимо) классов, которые уже
отвечают за выполнение запроса к конкретному эндпоинту стороннего api. Во время выполнения команды, также будут заданы
уточняющие вопросы, необходимые для генерации необходимых классов.

### Ручная конфигурация клиента

Как пример возьмём вымышленный эндпоинт `https://example.com/api/status`, который присылает ответ:

```json
{
  "status": true
}
```

1. Создадим класс-клиент:

```php
<?php

use ApiClientBundle\Model\AbstractClientConfiguration;

class ExampleClient extends AbstractClientConfiguration
{
    public function domain(): string
    {
        return 'example.com';
    }

    public function scheme(): string
    {
        return self::SCHEME_SSL;
    }

    public function isAsync(): bool
    {
        return false;
    }
}
```

2. Создадим класс конфигуратор запроса:

```php
<?php

use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use ApiClientBundle\Model\AbstractQuery;
use ApiClientBundle\Model\GenericErrorResponse;use Symfony\Component\HttpFoundation\Request;

class StatusQuery extends AbstractQuery
{
    public function method(): string
    {
        return Request::METHOD_GET;
    }

    public function path(): string
    {
        return '/api/status';
    }

    public function support(ClientConfigurationInterface $clientConfiguration): bool
    {
        return $clientConfiguration instanceof ExampleClient;
    }

    public function responseClassName(): string
    {
        return StatusResponse::class;
    }

    public function errorResponseClassName(): string
    {
        return GenericErrorResponse::class;
    }
}
```

3. Создадим класс DTO ответа:
```php
<?php

use ApiClientBundle\Model\AbstractResponse;

class StatusResponse extends AbstractResponse
{
    protected bool $status;

    public function getStatus(): bool
    {
        return $this->status;
    }
}
```

На этом конфигурация клиента и запроса завершена!

Чтобы выполнить запрос, достаточно вызвать такой код:

```php
<?php

use ApiClientBundle\Service\ApiClientFactory;

// получаем ApiClientFactory через DI

/** @var ApiClientFactory $apiClientFactory */
$client = $apiClientFactory->use(ExampleClient::class);
$response = $client->request(new StatusQuery());

$response->getStatus();
```
