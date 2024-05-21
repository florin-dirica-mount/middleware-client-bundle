<?php

namespace Horeca\MiddlewareClientBundle\VO\Tenant;

use Horeca\MiddlewareCommonLib\Model\Restaurant\Restaurant;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class TenantShopMenuUploadBody
{

    #[Serializer\SerializedName("restaurant")]
    #[Serializer\Type(Restaurant::class)]
    #[Assert\NotNull(message: "app.parameter.restaurant.not_null")]
    public Restaurant $restaurant;

    #[Serializer\SerializedName("service_credentials")]
    #[Serializer\Type("array")]
    public array $providerCredentials = [];

    #[Serializer\SerializedName("tenant_shop_id")]
    #[Serializer\Type("string")]
    #[Assert\NotNull(message: "app.parameter.tenant_shop_id.not_null")]
    public string $tenantShopIdId;
}
