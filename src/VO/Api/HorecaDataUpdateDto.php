<?php

namespace Horeca\MiddlewareClientBundle\VO\Api;

abstract class HorecaDataUpdateDto
{
    public string $serviceIdentifier;
    public \DateTime $time;
    abstract function getPayload();

    public function __construct()
    {
        $this->time = new \DateTime();
    }


}
