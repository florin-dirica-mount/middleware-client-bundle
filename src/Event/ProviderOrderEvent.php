<?php

namespace Horeca\MiddlewareClientBundle\Event;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Symfony\Contracts\EventDispatcher\Event;

class ProviderOrderEvent extends Event
{
    public const ORDER_RECEIVED = 'hmc.provider.order.received';
    public const ORDER_MAPPED = 'hmc.provider.order.mapped';

    public function __construct(private OrderNotification $orderNotification)
    {
    }

    public function getOrderNotification(): OrderNotification
    {
        return $this->orderNotification;
    }
}