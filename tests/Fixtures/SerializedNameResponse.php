<?php

declare(strict_types=1);

namespace ApiClientBundle\Tests\Fixtures;

use ApiClientBundle\Model\AbstractResponse;
use Symfony\Component\Serializer\Annotation\SerializedName;

class SerializedNameResponse extends AbstractResponse
{
    #[SerializedName('foo_bar')]
    public string $renamed;
}
