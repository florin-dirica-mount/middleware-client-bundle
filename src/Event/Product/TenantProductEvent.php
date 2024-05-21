<?php

namespace Horeca\MiddlewareClientBundle\Event\Product;

use Horeca\MiddlewareClientBundle\Entity\ProductNotification;
use Symfony\Contracts\EventDispatcher\Event;

class TenantProductEvent extends Event
{
    public const PRODUCT_RECEIVED = 'hmc.tenant.product.received';
    public const PRODUCT_MAPPED = 'hmc.tenant.product.mapped';

    public function __construct(private ProductNotification $productNotification)
    {
    }

    public function getProductNotification(): ProductNotification
    {
        return $this->productNotification;
    }
}
