<?php

namespace Horeca\MiddlewareClientBundle\Service;

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
     * @param ProviderCredentialsInterface $credentials
     * @return bool
     */
    public function initializeShop(ProviderCredentialsInterface $credentials): bool;

}
