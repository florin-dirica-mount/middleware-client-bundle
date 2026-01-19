<?php

namespace Horeca\MiddlewareClientBundle\Enum;

class TenantWebhookName
{
    const WEBHOOK_SHOPPING_CART_SEND = 'tenant.webhook.shopping_cart.send';
    const WEBHOOK_SHOPPING_CART_UPDATE_SEND = 'tenant.webhook.shopping_cart_update.send';
    const WEBHOOK_ORDER_NOTIFICATION_EVENT = 'tenant.webhook.order_notification_event';
    const WEBHOOK_GET_PRODUCTS = 'tenant.webhook.get_products';
    const WEBHOOK_EVENTS = 'tenant.webhook.events';

    const WEBHOOK_MENU_CATEGORY_SEND = 'tenant.webhook.menu.category.send';
    const WEBHOOK_MENU_SWITCH_WITH_TMP = 'tenant.webhook.menu.switch_with_tmp';
    const WEBHOOK_MENU_CREATE_TMP = 'tenant.webhook.menu.create_tmp';
    const WEBHOOK_MENU_REMOVE_TMP = 'tenant.webhook.menu.remove_tmp';
    const WEBHOOK_BULK_UPDATE_PRODUCTS_AVAILABILITY = 'tenant.webhook.bulk_update_products_availability';

    protected function __construct()
    {
    }
}
