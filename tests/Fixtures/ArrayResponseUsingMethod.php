<?php

declare(strict_types=1);

namespace ApiClientBundle\Tests\Fixtures;

use ApiClientBundle\Model\AbstractResponse;

class ArrayResponseUsingMethod extends AbstractResponse
{
    // @phpstan-ignore-next-line тут специально нет типа, т.к. проверяем работу через addFile()
    public array $files;

    public function addFile(TestFile $file): void
    {
        $this->files[] = $file;
    }
}
