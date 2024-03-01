<?php

namespace Horeca\MiddlewareClientBundle\Enum;

class TenantWebhookName
{
    const WEBHOOK_SHOPPING_CART_SEND = 'tenant.webhook.shopping_cart.send';
    const WEBHOOK_ORDER_NOTIFICATION_EVENT = 'tenant.webhook.order_notification_event';

    protected function __construct() { }
}