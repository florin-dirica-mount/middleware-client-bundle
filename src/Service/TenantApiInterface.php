<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;

interface TenantApiInterface
{
    const PATH_NOTIFICATION_EVENT = '/middleware/notification/event';
    const PATH_SEND_SHOPPING_CART = '/middleware/order/%s';
    const PATH_CONFIRM_PROVIDER_NOTIFIED = '/middleware/cart/%s/confirm-provider-notified';

    /**
     * @deprecated use sendOrderNotificationEvent
     */
    public function confirmProviderNotified(OrderNotification $notification): bool;

    public function sendShoppingCart(Tenant $tenant, ShoppingCart $cart, $restaurantId): SendShoppingCartResponse;

    public function sendOrderNotificationEvent(string $event, OrderNotification $notification): void;
}