<?php

namespace Horeca\MiddlewareClientBundle\Event;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Symfony\Contracts\EventDispatcher\Event;

class TenantOrderEvent extends Event
{
    public const ORDER_RECEIVED = 'hmc.tenant.order.received';
    public const ORDER_MAPPED = 'hmc.tenant.order.mapped';

    public function __construct(private OrderNotification $orderNotification)
    {
    }

    public function getOrderNotification(): OrderNotification
    {
        return $this->orderNotification;
    }
}