<?php

namespace Horeca\MiddlewareClientBundle\Message;

use Horeca\MiddlewareClientBundle\Entity\MappingNotification;

abstract class MappingNotificationMessage
{
    protected string $notificationId;

    public function __construct(MappingNotification $order)
    {
        $this->notificationId = $order->getId();
    }

    public function getNotificationId(): string
    {
        return $this->notificationId;
    }
}
