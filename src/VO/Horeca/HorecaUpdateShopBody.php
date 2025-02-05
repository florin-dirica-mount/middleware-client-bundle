<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Horeca\MiddlewareCommonLib\Model\Restaurant\Restaurant;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaUpdateShopBody
{


    #[Serializer\SerializedName("tenant_shop_id")]
    #[Serializer\Type("string")]
    #[Assert\NotNull(message: "app.parameter.tenant_shop_id.not_null")]
    public string $tenantShopId;

    #[Serializer\SerializedName("shop")]
    #[Serializer\Type(Restaurant::class)]
    #[Assert\NotNull(message: "app.parameter.shop.not_null")]
    public Restaurant $shop;




}
