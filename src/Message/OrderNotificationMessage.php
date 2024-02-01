<?php

namespace Horeca\MiddlewareClientBundle\Message;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;

class OrderNotificationMessage
{
    const TRANSPORT_DEFAULT = 'hmc_order_notification';

    private string $orderNotificationId;

    public function __construct(OrderNotification $order)
    {
        $this->orderNotificationId = $order->getId();
    }

    public function getOrderNotificationId(): string
    {
        return $this->orderNotificationId;
    }
}
