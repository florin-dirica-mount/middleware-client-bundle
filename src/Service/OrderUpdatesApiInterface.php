<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;

interface OrderUpdatesApiInterface
{
    public function mapTenantOrderUpdateToProvider(OrderNotification $notification): OrderNotification;

    public function sendTenantOrderToProvider(OrderNotification $notification): OrderNotification;


}
