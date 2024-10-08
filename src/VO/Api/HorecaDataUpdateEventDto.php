<?php

namespace Horeca\MiddlewareClientBundle\VO\Api;

abstract class HorecaDataUpdateEventDto
{
    public string $serviceIdentifier;
    public string $event;
    public \DateTime $time;

    abstract function getData();

    public function __construct()
    {
        $this->time = new \DateTime();
    }


}
