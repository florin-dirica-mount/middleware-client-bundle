<?php

namespace Horeca\MiddlewareClientBundle\Message;

final class MessageTransports
{
    public const SEND_PROVIDER_ORDER_TO_TENANT = 'hmc_external_service_order_notification';
    public const MAP_TENANT_ORDER_TO_PROVIDER = 'hmc_map_tenant_order_to_provider';
    public const SEND_TENANT_ORDER_TO_PROVIDER = 'hmc_tenant_order_send_to_provider';
    public const ORDER_NOTIFICATION_EVENT = 'hmc_order_notification_event';
    public const SYNC_PROVIDER_PRODUCTS_EVENT = 'hmc_sync_provider_products_event';
    public const SYNC_TENANT_PRODUCTS_EVENT = 'hmc_sync_tenant_products_event';

    private function __construct() { }
}
