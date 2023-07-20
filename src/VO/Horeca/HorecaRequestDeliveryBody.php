<?php

namespace App\VO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaRequestDeliveryBody
{

    #[Serializer\SerializedName("form")]
    #[Serializer\Type(HorecaRequestDeliveryForm::class)]
    #[Assert\NotNull(message: "app.parameter.cart.not_null")]
    public HorecaRequestDeliveryForm $form;

    #[Serializer\SerializedName("service_credentials")]
    #[Serializer\Type("array")]
    #[Assert\NotNull(message: "app.parameter.provider_credentials.not_null")]
    public array $providerCredentials = [];

}
