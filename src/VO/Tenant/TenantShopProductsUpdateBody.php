<?php

namespace Horeca\MiddlewareClientBundle\VO\Tenant;

use Horeca\MiddlewareCommonLib\Model\Menu\Product;
use Horeca\MiddlewareCommonLib\Model\Menu\SubProduct;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class TenantShopProductsUpdateBody extends TenantShopIdAwareBody
{

    #[Serializer\SerializedName("products")]
    #[Assert\NotNull(message: "app.parameter.products.not_null")]
    /** @var $products Product[] */
    public array $products = [];

    #[Serializer\SerializedName("sub_products")]
    /** @var $subproducts SubProduct[] */
    public array $subproducts = [];

    #[Serializer\SerializedName("service_credentials")]
    /** @var array<string, mixed> */
    public array $providerCredentials = [];


}
