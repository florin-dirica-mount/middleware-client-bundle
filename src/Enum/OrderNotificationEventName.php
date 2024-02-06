<?php

namespace Horeca\MiddlewareClientBundle\Enum;

final class OrderNotificationEventName
{
    public const MAPPING_FAILED = 'hmc.event.order_notification.mapping_failed';
    public const MAPPING_COMPLETED = 'hmc.event.order_notification.mapping_completed';

    public const PROVIDER_NOTIFICATION_FAILED = 'hmc.event.order_notification.provider.notification_failed';
    public const PROVIDER_NOTIFIED = 'hmc.event.order_notification.provider.notified';

    public const TENANT_NOTIFICATION_FAILED = 'hmc.event.order_notification.tenant.notification_failed';
    public const TENANT_NOTIFIED = 'hmc.event.order_notification.tenant.notified';

    private function __construct() { }
}