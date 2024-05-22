<?php

namespace Horeca\MiddlewareClientBundle\VO\Tenant;

use Horeca\MiddlewareCommonLib\Model\Menu\Product;
use Horeca\MiddlewareCommonLib\Model\Menu\SubProduct;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class TenantShopProductsUpdateBody extends TenantShopIdAwareBody
{

    #[Serializer\SerializedName("products")]
    #[Serializer\Type(Product::class)]
    #[Assert\NotNull(message: "app.parameter.products.not_null")]
    public array $products = [];

    #[Serializer\SerializedName("sub_products")]
    #[Serializer\Type(SubProduct::class)]
    public array $subproducts = [];

    #[Serializer\SerializedName("service_credentials")]
    #[Serializer\Type("array")]
    public array $providerCredentials = [];


}
