<?php

namespace ApiClientBundle\Serializer;

use ApiClientBundle\Attribute\CollectionResponseField;
use ApiClientBundle\Client\CollectionResponseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class CollectionDenormalizer implements DenormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    private const DEFAULT_PROPERTY_NAME = 'items';

    /**
     * @param array<mixed> $context
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        $reflectionClass = new \ReflectionClass($type);
        $reflectionInterfaces = $reflectionClass->getInterfaces();
        /** @var CollectionResponseField|null $attribute */
        $attribute = null;
        foreach ($reflectionInterfaces as $reflectionInterface) {
            $interfaceAttributes = $reflectionInterface->getAttributes(CollectionResponseField::class);
            foreach ($interfaceAttributes as $interfaceAttribute) {
                if (!is_a($interfaceAttribute->getName(), CollectionResponseField::class, true)) {
                    continue;
                }

                $attribute = $interfaceAttribute->newInstance();

                break;
            }

            if (null !== $attribute) {
                break;
            }
        }

        $classAttributes = $reflectionClass->getAttributes(CollectionResponseField::class);
        foreach ($classAttributes as $classAttribute) {
            if (!is_a($classAttribute->getName(), CollectionResponseField::class, true)) {
                continue;
            }

            $attribute = $classAttribute->newInstance();

            break;
        }

        $field = $attribute->propertyName ?? self::DEFAULT_PROPERTY_NAME;
        $data[$field] = $data;

        /* @phpstan-ignore-next-line */
        return $this->serializer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return array_is_list($data) && is_a($type, CollectionResponseInterface::class, true);
    }
}
