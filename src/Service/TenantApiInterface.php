<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaRequestDeliveryBody;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;
use Horeca\MiddlewareCommonLib\Model\Protocol\ShoppingCartStatusUpdate;

interface TenantApiInterface
{
    /**
     * @deprecated use sendOrderNotificationEvent
     */
    public function confirmProviderNotified(OrderNotification $notification): bool;

    public function sendShoppingCart(Tenant $tenant, ShoppingCart $cart, ?string $shopId, ?string $viewUrl): SendShoppingCartResponse;

    public function sendShoppingCartUpdate(Tenant $tenant, ShoppingCartStatusUpdate $object, ?string $viewUrl): SendShoppingCartResponse;

    public function sendOrderNotificationEvent(string $event, OrderNotification $notification): void;

}
