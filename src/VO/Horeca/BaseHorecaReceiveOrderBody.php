<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Horeca\MiddlewareClientBundle\Enum\OrderTransferMode;
use Horeca\MiddlewareClientBundle\Validator\Constraints\ValidShoppingCartConfiguration;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class BaseHorecaReceiveOrderBody
{

    #[Serializer\SerializedName("cart")]
    #[Assert\NotNull(message: "app.parameter.cart.not_null")]
    #[ValidShoppingCartConfiguration]
    public ?ShoppingCart $cart = null;

    #[Serializer\SerializedName("service_credentials")]
    public array $providerCredentials = [];

    #[Serializer\SerializedName("transfer_mode")]
    public ?string $transferMode = OrderTransferMode::ASYNC;

}
