<?php

namespace Horeca\MiddlewareClientBundle\Message;


use Horeca\MiddlewareClientBundle\Entity\OrderNotification;

class ExternalServiceOrderNotificationMessage
{
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
