<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\VO\Provider\BaseProviderOrderResponse;

interface OrderUpdatesApiInterface
{
    public function mapTenantOrderUpdateToProvider(OrderNotification $notification): mixed;

    public function sendTenantOrderUpdateToProvider(OrderNotification $notification): ?BaseProviderOrderResponse;


}
