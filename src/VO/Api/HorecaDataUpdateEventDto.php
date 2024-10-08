<?php

namespace Horeca\MiddlewareClientBundle\VO\Api;

abstract class HorecaDataUpdateEventDto
{
    public string $event;
    public \DateTime $time;

    abstract function getData();


}
