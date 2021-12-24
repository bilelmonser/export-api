<?php

namespace App\Service\App;

use App\Entity\Treezor\Auth\Token;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SerializeService
{
    private SerializerInterface $serializer;
    private ObjectNormalizer $normalizer;

    /**
     * SerializeService constructor.
     *
     * @param SerializerInterface $serializer
     * @param ObjectNormalizer $normalizer
     */
    public function __construct(SerializerInterface $serializer, ObjectNormalizer $normalizer)
    {
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
    }


    /**
     * @param mixed $data
     * @param string|null $format
     * @param array|null $context
     * @return string|null
     */
    public function SerializeContent($data, ?string $format = 'json', ?array $context = [])
    {
        $dataSerialized = $this->serializer->serialize($data, $format, $context);
        return $dataSerialized;
    }

    /**
     * @param mixed $data
     * payload of the data received
     * @param string $className
     * Class name
     * @param string|null $format
     * The format type : json , xml etc...
     * @param array|null $context
     * array of context
     */
    public function DeserializeContent($data, string $className, ?string $format = 'json', ?array $context = [])
    {
        return $this->serializer->deserialize($data, $className, $format, $context);
    }

    /**
     * @param mixed $data
     * @param string|null $format
     * @param array|null $context
     *
     * @return array|string|int|float|bool|\ArrayObject|null
     */
    public function NormalizeContent($data, ?string $format = 'array', ?array $context = [])
    {
        return $this->normalizer->normalize($data, $format, $context);
    }

    /**
     * @param mixed $data
     * @param string $className
     * @param string|null $format
     * @param array|null $context
     */
    public function DenormalizeContent($data, string $className, ?string $format = 'json', ?array $context = [])
    {
        return $this->normalizer->denormalize($data, $className, $format, $context);
    }
}
