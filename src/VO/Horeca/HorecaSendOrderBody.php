<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaSendOrderBody
{

    #[Serializer\SerializedName("cart")]
    #[Serializer\Type(ShoppingCart::class)]
    #[Assert\NotNull(message: "app.parameter.cart.not_null")]
    public ?ShoppingCart $cart = null;

    #[Serializer\SerializedName("service_credentials")]
    #[Serializer\Type("array")]
    public array $providerCredentials = [];

}
