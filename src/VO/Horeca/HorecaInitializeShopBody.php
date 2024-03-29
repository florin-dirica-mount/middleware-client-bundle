<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaInitializeShopBody
{


    #[Serializer\SerializedName("horeca_external_service_id")]
    #[Serializer\Type("string")]
    #[Assert\NotNull(message: "app.parameter.horeca_external_service_id.not_null")]
    public string $horecaExternalServiceId;

    #[Serializer\SerializedName("service_credentials")]
    #[Serializer\Type("array")]
    #[Assert\NotNull(message: "app.parameter.provider_credentials.not_null")]
    public array $providerCredentials = [];

}
