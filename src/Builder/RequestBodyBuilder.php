<?php

namespace ApiClientBundle\Builder;

use ApiClientBundle\Client\QueryInterface;
use ApiClientBundle\Client\ServiceInterface;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use Psr\Http\Message\StreamInterface;

class RequestBodyBuilder implements QueryBuilderInterface
{
    /**
     * @return array{stream: StreamInterface, boundary: null|string}|null
     */
    public static function build(QueryInterface $query, ServiceInterface $service): ?array
    {
        $stream = null;
        $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        $boundary = null;
        $body = $query->getBody();
        if (null !== $body && count($query->getParameters()) === 0) {
            return [
                'stream' => $streamFactory->createStream($body),
                'boundary' => null,
            ];
        }

        $denormalizedParameters = [];
        foreach ($query->getParameters() as $key => $value) {
            $denormalizedParameters[] = $key . '=' . $value;
        }
        if (count($denormalizedParameters) > 0) {
            $body = \implode('&', $denormalizedParameters);
            $stream = $streamFactory->createStream($body);
        }

        if ($query->getFiles() !== null) {
            $multipartStreamBuilder = new MultiPartStreamBuilder($streamFactory);
            if (null !== $stream) {
                $multipartStreamBuilder->addData($stream);
            }
            foreach ($query->getFiles() as $key => $item) {
                if (is_string($item)) {
                    $file = new \SplFileInfo($item);
                    $multipartStreamBuilder->addResource(
                        $key,
                        fopen($file->getRealPath(), 'rb'),
                        ['filename' => $file->getFilename()]
                    );

                    continue;
                }

                foreach ($item as $path) {
                    $file = new \SplFileInfo($path);
                    $multipartStreamBuilder->addResource(
                        $key,
                        fopen($file->getRealPath(), 'r'),
                        ['filename' => $file->getFilename()]
                    );
                }
            }
            $stream = $multipartStreamBuilder->build();
            $boundary = $multipartStreamBuilder->getBoundary();
        }

        if (null === $stream) {
            return null;
        }

        return [
            'stream' => $stream,
            'boundary' => $boundary,
        ];
    }
}
