<?php

namespace App\Service\App;

use Symfony\Component\Serializer\SerializerInterface;

class SerializeService
{
    private SerializerInterface $serializer;


    /**
     * SerializeService constructor.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }


    /**
     * @param mixed $data
     * @param string|null $format
     * @param array|null $context
     * @return string|null
     */
    public function serializeContent($data, ?string $format = 'json', ?array $context = []): ?string
    {
        return $this->serializer->serialize($data, $format, $context);
    }

}
