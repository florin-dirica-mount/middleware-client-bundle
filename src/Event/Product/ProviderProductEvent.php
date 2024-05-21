<?php

namespace Horeca\MiddlewareClientBundle\Event\Product;

use Horeca\MiddlewareClientBundle\Entity\MenuNotification;
use Horeca\MiddlewareClientBundle\Entity\ProductNotification;
use Symfony\Contracts\EventDispatcher\Event;

class ProviderProductEvent extends Event
{
    public const PRODUCT_RECEIVED = 'hmc.provider.product.received';
    public const PRODUCT_MAPPED = 'hmc.provider.product.mapped';

    public function __construct(private ProductNotification $productNotification)
    {
    }

    public function getProductNotification(): ProductNotification
    {
        return $this->productNotification;
    }
}
