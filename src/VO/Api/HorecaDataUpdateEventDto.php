<?php

namespace Horeca\MiddlewareClientBundle\VO\Api;

abstract class HorecaDataUpdateEventDto
{
    public string $event;

    abstract function getData();


}
