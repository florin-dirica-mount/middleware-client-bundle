<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Product\ProductMapping;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;

interface TenantApiInterface
{
    /**
     * Returns the corresponding product mappings for each ShoppingCartEntry in the given ShoppingCart
     *
     * @return ProductMapping[]
     */
    public function getShoppingCartProductMappings(ShoppingCart $cart): array;

    public function confirmProviderNotified(OrderNotification $notification): bool;

    public function sendShoppingCart(Tenant $tenant, ShoppingCart $cart, $restaurantId): SendShoppingCartResponse;

}