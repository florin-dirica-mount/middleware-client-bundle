<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaRequestDeliveryBody;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;

interface TenantApiInterface
{
    /**
     * @deprecated use sendOrderNotificationEvent
     */
    public function confirmProviderNotified(OrderNotification $notification): bool;

    public function sendShoppingCart(Tenant $tenant, ShoppingCart $cart, ?string $shopId, ?string $viewUrl): SendShoppingCartResponse;

    public function sendShoppingCartUpdate(Tenant $tenant, $object, ?string $viewUrl): SendShoppingCartResponse;

    public function sendOrderNotificationEvent(string $event, OrderNotification $notification): void;

    public function initializeShop(Tenant $tenant, string $tenantShopId, string $providerShopId, ?string $shopName): bool;

    /**
     * @param ProviderCredentialsInterface $credentials
     */
    public function requestDelivery(HorecaRequestDeliveryBody $body, $credentials): bool;

    public function generateNotificationViewUrl(OrderNotification $notification): ?string;
}
