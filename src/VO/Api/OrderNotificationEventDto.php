<?php

namespace Horeca\MiddlewareClientBundle\VO\Api;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Enum\SerializationGroups;
use JMS\Serializer\Annotation as Serializer;

final class OrderNotificationEventDto
{
    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView])]
    public string $event;

    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView])]
    public OrderNotification $notification;

    public function __construct(string $event, OrderNotification $notification)
    {
        $this->event = $event;
        $this->notification = $notification;
    }
}
