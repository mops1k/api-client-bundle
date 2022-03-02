<?php

declare(strict_types=1);

namespace ApiClientBundle\Tests\Fixtures;

use ApiClientBundle\Model\AbstractResponse;

class ArrayResponseUsingPhpDoc extends AbstractResponse
{
    /**
     * @var TestFile[]
     */
    public array $files;
}
