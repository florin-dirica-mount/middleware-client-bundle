<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\VO\Horeca\HorecaRequestDeliveryBody;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;

interface RequestDeliveryApiInterface
{
    /**
     * @param ProviderCredentialsInterface $credentials
     */
    public function requestDelivery(HorecaRequestDeliveryBody $body, $credentials): bool;

}
