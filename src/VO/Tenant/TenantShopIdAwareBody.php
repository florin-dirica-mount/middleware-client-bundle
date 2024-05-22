<?php

namespace Horeca\MiddlewareClientBundle\VO\Tenant;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

class TenantShopIdAwareBody
{

    #[Serializer\SerializedName("tenant_shop_id")]
    #[Serializer\Type("string")]
    #[Assert\NotNull(message: "app.parameter.tenant_shop_id.not_null")]
    public string $tenantShopId;

}
