<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use JMS\Serializer\Annotation as Serializer;

class Coordiantes
{

    #[Serializer\SerializedName("latitude")]
    #[Serializer\Type("float")]
    public float $latitude;

    #[Serializer\SerializedName("longitude")]
    #[Serializer\Type("float")]
    public float $longitude;

}
