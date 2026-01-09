<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Horeca\MiddlewareCommonLib\Model\Restaurant\Restaurant;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaUpdateShopBody
{


    #[Serializer\SerializedName("tenant_shop_id")]
    #[Assert\NotNull(message: "app.parameter.tenant_shop_id.not_null")]
    public string $tenantShopId;

    #[Serializer\SerializedName("shop")]
    #[Assert\NotNull(message: "app.parameter.shop.not_null")]
    public Restaurant $shop;




}
