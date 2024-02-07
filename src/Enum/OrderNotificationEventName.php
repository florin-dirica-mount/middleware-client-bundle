<?php

namespace Horeca\MiddlewareClientBundle\Enum;

final class OrderNotificationEventName
{
    public const MAPPING_FAILED = 'hmc.order_notification.mapping_failed';
    public const MAPPING_COMPLETED = 'hmc.order_notification.mapping_completed';

    public const PROVIDER_NOTIFICATION_FAILED = 'hmc.order_notification.provider_notification_failed';
    public const PROVIDER_NOTIFIED = 'hmc.order_notification.provider_notified';

    public const TENANT_NOTIFICATION_FAILED = 'hmc.order_notification.tenant_notification_failed';
    public const TENANT_NOTIFIED = 'hmc.order_notification.tenant_notified';

    private function __construct() { }
}