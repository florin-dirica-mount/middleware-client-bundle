<?php

namespace Horeca\MiddlewareClientBundle\Message;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;

abstract class OrderNotificationMessage
{
    protected string $orderNotificationId;

    public function __construct(OrderNotification $order)
    {
        $this->orderNotificationId = $order->getId();
    }

    public function getOrderNotificationId(): string
    {
        return $this->orderNotificationId;
    }
}
