<?php

namespace Horeca\MiddlewareClientBundle\Message;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;

/**
 * Message is dispatched after a Tenant order is sent to the provider
 */
class OrderNotificationEventSyncMessage extends MappingNotificationMessage
{
    public function __construct(protected string $event, OrderNotification $order)
    {
        parent::__construct($order);
    }

    public function getEvent(): string
    {
        return $this->event;
    }
}
