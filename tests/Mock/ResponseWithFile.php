<?php

namespace ApiClientBundle\Tests\Mock;

use ApiClientBundle\Client\ResponseInterface;

class ResponseWithFile implements ResponseInterface
{
    public function __construct(
        public readonly string $FileName,
        public readonly string $FileExt,
        public readonly int $FileSize,
        public readonly string $FileId,
        public readonly string $Url,
    ) {
    }
}
