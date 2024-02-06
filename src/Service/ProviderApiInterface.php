<?php

namespace Horeca\MiddlewareClientBundle\Service;

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
     */
    public function getProviderOrderClass(): string;

    /**
     * Return the classname of the provider credentials model
     */
    public function getProviderCredentialsClass(): string;

    /**
     * Saves the order data into the provider system and returns the external order ID, if it is applicable
     *
     * @var ProviderOrderInterface $order
     */
    public function sendOrderToProvider($order, ProviderCredentialsInterface $credentials): ?BaseProviderOrderResponse;

    /**
     * Handles the mapping between ShoppingCart and ProviderOrder models
     *
     * @return ProviderOrderInterface
     */
    public function mapShoppingCartToProviderOrder(Tenant $tenant, ShoppingCart $cart);

    /**
     * @param Tenant $tenant
     * @param ProviderOrderInterface $order
     * @return ShoppingCart
     * Handles the mapping between ProviderOrder and ShoppingCart models
     */
    public function mapProviderOrderToShoppingCart(Tenant $tenant, $order): ShoppingCart;

    /**
     * @param string $horecaExternalServiceId
     * @param ProviderCredentialsInterface $credentials
     * @return bool
     */
    public function initializeShop(string $horecaExternalServiceId, $credentials): bool;

    /**
     * @param HorecaRequestDeliveryBody $body
     * @param ProviderCredentialsInterface $credentials
     * @return bool
     */
    public function requestDelivery(HorecaRequestDeliveryBody $body, $credentials): bool;

}
