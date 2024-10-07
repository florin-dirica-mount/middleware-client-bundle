<?php

namespace Horeca\MiddlewareClientBundle\VO\Api;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Enum\SerializationGroups;
use JMS\Serializer\Annotation as Serializer;

final class OrderNotificationResponseDataDto
{
    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView,SerializationGroups::ProviderOrderNotificationView])]
    public bool $success = true;

    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView,SerializationGroups::ProviderOrderNotificationView])]
    public bool $confirmed = true;

    #[Serializer\Groups([SerializationGroups::TenantOrderNotificationView,SerializationGroups::ProviderOrderNotificationView])]
    public OrderNotification $data;

    public function __construct(OrderNotification $data, bool $success = true, bool $confirmed = true)
    {
        $this->data = $data;
        $this->success = $success;
        $this->confirmed = $confirmed;
    }

}
