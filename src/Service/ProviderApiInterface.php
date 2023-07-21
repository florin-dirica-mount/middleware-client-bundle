<?php

namespace Horeca\MiddlewareClientBundle\Service;

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
     */
    public function saveOrder(ProviderOrderInterface $order, ProviderCredentialsInterface $credentials): ?BaseProviderOrderResponse;

    /**
     * Handles the mapping between ShoppingCart and ProviderOrder models
     */
    public function mapShoppingCartToProviderOrder(ShoppingCart $cart): ProviderOrderInterface;

    /**
     * @param $providerOrder
     * @return ShoppingCart
     * Handles the mapping between ProviderOrder and ShoppingCart models
     */
    public function mapProviderOrderToShoppingCart($providerOrder): ShoppingCart;

    /**
     * @param string $horecaExternalServiceId
     * @param ProviderCredentialsInterface $credentials
     * @return bool
     */
    public function initializeShop(string $horecaExternalServiceId, ProviderCredentialsInterface $credentials): bool;

    /**
     * @param HorecaRequestDeliveryBody $body
     * @param ProviderCredentialsInterface $credentials
     * @return bool
     */
    public function requestDelivery(HorecaRequestDeliveryBody $body, ProviderCredentialsInterface $credentials): bool;

}
