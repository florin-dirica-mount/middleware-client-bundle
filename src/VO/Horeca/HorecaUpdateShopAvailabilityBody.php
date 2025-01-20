<?php

namespace Horeca\MiddlewareClientBundle\VO\Horeca;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class HorecaUpdateShopAvailabilityBody
{



    #[Serializer\SerializedName("tenant_shop_id")]
    #[Serializer\Type("string")]
    #[Assert\NotNull(message: "app.parameter.tenant_shop_id.not_null")]
    public string $tenantShopId;

    #[Serializer\SerializedName("open")]
    #[Serializer\Type("bool")]
    #[Assert\NotNull(message: "app.parameter.open.not_null")]
    public bool $open;




}
