<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaRequestDeliveryBody;
use Horeca\MiddlewareClientBundle\VO\Provider\BaseProviderOrderResponse;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderOrderInterface;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderOrderPayloadInterface;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;

interface ProviderApiInterface
{


    public function getMiddlewareToProviderOrderClass(): string;

    public function getProviderToMiddlewareOrderClass(): string;

    /**
     * Saves the order data into the provider system and returns the external order ID, if it is applicable
     *
     * @param ProviderOrderPayloadInterface $order
     * @param ProviderCredentialsInterface $credentials
     */
    public function sendOrderToProvider($order, $credentials): ?BaseProviderOrderResponse;

    /**
     * Handles the mapping between ShoppingCart and ProviderOrder models
     *
     * @param Tenant $tenant
     * @param ShoppingCart $cart
     * @return ProviderOrderPayloadInterface
     */
    public function mapShoppingCartToProviderOrder(Tenant $tenant, ShoppingCart $cart): ProviderOrderPayloadInterface;

    /**
     * Handles the mapping between ProviderOrder and ShoppingCart models
     *
     * @param ProviderOrderInterface $order
     */
    public function mapProviderOrderToShoppingCart(Tenant $tenant, $order): ShoppingCart;

    public function generateNotificationViewUrl(OrderNotification $notification): ?string;

}
