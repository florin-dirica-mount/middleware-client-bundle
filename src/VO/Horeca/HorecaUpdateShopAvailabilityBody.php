<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaUpdateShopAvailabilityBody
{



    #[Serializer\SerializedName("tenant_shop_id")]
    #[Assert\NotNull(message: "app.parameter.tenant_shop_id.not_null")]
    public string $tenantShopId;

    #[Serializer\SerializedName("open")]
    #[Assert\NotNull(message: "app.parameter.open.not_null")]
    public bool $open;




}
