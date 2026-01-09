<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Symfony\Component\Serializer\Attribute as Serializer;

class Coordiantes
{

    #[Serializer\SerializedName("latitude")]
    public float $latitude;

    #[Serializer\SerializedName("longitude")]
    public float $longitude;

}
