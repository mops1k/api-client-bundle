<?php

namespace ApiClientBundle\Model;

use ApiClientBundle\Interfaces\GenericErrorResponseInterface;

class GenericErrorResponse extends AbstractResponse implements GenericErrorResponseInterface
{
    // todo: разобраться почему это работает только с public: private/protected не заполняется сериализатором
    // хотя PropertyNormalizer вроде должен и private заполнять
    // todo: и нужно на это написать тест
    protected string $rawContent;

    public function getRawContent(): string
    {
        return $this->rawContent;
    }
}
