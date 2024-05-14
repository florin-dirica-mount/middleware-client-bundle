<?php

namespace Horeca\MiddlewareClientBundle\Enum;

class TenantWebhookName
{
    const WEBHOOK_SHOPPING_CART_SEND = 'tenant.webhook.shopping_cart.send';
    const WEBHOOK_ORDER_NOTIFICATION_EVENT = 'tenant.webhook.order_notification_event';
    const WEBHOOK_GET_PRODUCTS = 'tenant.webhook.get_products';


    protected function __construct() { }
}
