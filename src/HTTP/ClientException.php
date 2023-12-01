<?php

namespace ApiClientBundle\HTTP;

use Psr\Http\Client\ClientExceptionInterface;

final class ClientException extends \Exception implements ClientExceptionInterface
{
}
