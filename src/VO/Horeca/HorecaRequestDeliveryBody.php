<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaRequestDeliveryBody
{

    #[Serializer\SerializedName("form")]
    #[Assert\NotNull(message: "app.parameter.cart.not_null")]
    public HorecaRequestDeliveryForm $form;

    #[Serializer\SerializedName("service_credentials")]
    #[Assert\NotNull(message: "app.parameter.provider_credentials.not_null")]
    public array $providerCredentials = [];

}
