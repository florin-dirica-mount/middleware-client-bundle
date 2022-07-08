<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Framework;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

trait SerializerDI
{
    protected SerializerInterface $serializer;

    /**
     * @required
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function convertObjectToArray($object, array $groups = []): array
    {
        $json = $this->serializeJson($object, $groups);

        return json_decode($json, true);
    }

    public function serializeJson($data, array $groups = []): string
    {
        $ctx = count($groups) > 0 ? SerializationContext::create()->setGroups($groups) : null;

        return $this->serializer->serialize($data, 'json', $ctx);
    }

    public function deserializeJson(string $json, string $type, array $groups = [])
    {
        $ctx = count($groups) > 0 ? DeserializationContext::create()->setGroups($groups) : null;

        return $this->serializer->deserialize($json, $type, 'json', $ctx);
    }

    public function deserializeArray(array $array, string $type, array $groups = [])
    {
        $ctx = count($groups) > 0 ? DeserializationContext::create()->setGroups($groups) : null;

        return $this->serializer->deserialize($this->serializeJson($array), $type, 'json', $ctx);
    }
}
