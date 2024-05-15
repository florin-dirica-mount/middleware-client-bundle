<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Horeca\MiddlewareClientBundle\Enum\OrderTransferMode;
use Horeca\MiddlewareClientBundle\Validator\Constraints\ValidShoppingCartConfiguration;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Restaurant\Restaurant;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaRestaurantMenuUploadBody
{

    #[Serializer\SerializedName("restaurant")]
    #[Serializer\Type(Restaurant::class)]
    #[Assert\NotNull(message: "app.parameter.restaurant.not_null")]
    public Restaurant $restaurant ;

    #[Serializer\SerializedName("service_credentials")]
    #[Serializer\Type("array")]
    public array $providerCredentials = [];

    #[Serializer\SerializedName("horeca_external_service_id")]
    #[Serializer\Type("string")]
    #[Assert\NotNull(message: "app.parameter.horeca_external_service_id.not_null")]
    public string $horecaExternalServiceId;
}
