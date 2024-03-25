<?php

namespace Horeca\MiddlewareClientBundle\Event;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Symfony\Contracts\EventDispatcher\Event;

class OrderNotificationEvent extends Event
{
    public const TENANT_ORDER_RECEIVED = 'hmc.tenant.order.received';

    public function __construct(private OrderNotification $orderNotification)
    {
    }

    public function getOrderNotification(): OrderNotification
    {
        return $this->orderNotification;
    }
}