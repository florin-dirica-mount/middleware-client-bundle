<?php

namespace Horeca\MiddlewareClientBundle\VO\Tenant;

use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class TenantShopIdAwareBody
{

    #[Serializer\SerializedName("tenant_shop_id")]
    #[Assert\NotNull(message: "app.parameter.tenant_shop_id.not_null")]
    public string $tenantShopId;

}
