<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaRequestDeliveryBody;
use Horeca\MiddlewareClientBundle\VO\Provider\BaseProviderOrderResponse;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderOrderInterface;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;

interface ProviderApiInterface
{

    /**
     * Return the classname of the provider order model
     * @deprecated use getMiddlewareToProviderOrderClass and getProviderToMiddlewareOrderClass instead
     */
    public function getProviderOrderClass(): string;

    public function getMiddlewareToProviderOrderClass(): string;

    public function getProviderToMiddlewareOrderClass(): string;

    /**
     * Saves the order data into the provider system and returns the external order ID, if it is applicable
     *
     * @param ProviderOrderInterface $order
     * @param ProviderCredentialsInterface $credentials
     */
    public function sendOrderToProvider($order, $credentials): ?BaseProviderOrderResponse;

    /**
     * Handles the mapping between ShoppingCart and ProviderOrder models
     *
     * @return ProviderOrderInterface
     */
    public function mapShoppingCartToProviderOrder(Tenant $tenant, ShoppingCart $cart);

    /**
     * Handles the mapping between ProviderOrder and ShoppingCart models
     *
     * @param ProviderOrderInterface $order
     */
    public function mapProviderOrderToShoppingCart(Tenant $tenant, $order): ShoppingCart;

    public function initializeShop(Tenant $tenant, string $tenantShopId, string $providerShopId): bool;

    /**
     * @param ProviderCredentialsInterface $credentials
     */
    public function requestDelivery(HorecaRequestDeliveryBody $body, $credentials): bool;

    public function generateNotificationViewUrl(OrderNotification $notification): ?string;

}
