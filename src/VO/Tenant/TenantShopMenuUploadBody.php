<?php

namespace Horeca\MiddlewareClientBundle\VO\Tenant;

use Horeca\MiddlewareCommonLib\Model\Restaurant\Restaurant;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class TenantShopMenuUploadBody extends TenantShopIdAwareBody
{

    #[Serializer\SerializedName("restaurant")]
    #[Assert\NotNull(message: "app.parameter.restaurant.not_null")]
    public Restaurant $restaurant;

    #[Serializer\SerializedName("service_credentials")]
    public array $providerCredentials = [];


}
