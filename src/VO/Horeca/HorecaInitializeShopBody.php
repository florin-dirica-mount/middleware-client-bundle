<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaInitializeShopBody
{


    #[Serializer\SerializedName("shop_name")]
    public ?string $shopName = null;

    #[Serializer\SerializedName("tenant_shop_id")]
    #[Assert\NotNull(message: "app.parameter.tenant_shop_id.not_null")]
    public string $tenantShopId;

    #[Serializer\SerializedName("provider_shop_id")]
    #[Assert\NotNull(message: "app.parameter.provider_shop_id.not_null")]
    public string $providerShopId;


}
