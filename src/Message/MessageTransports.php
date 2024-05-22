<?php

namespace Horeca\MiddlewareClientBundle\Message;

final class MessageTransports
{
    //order
    public const SEND_PROVIDER_ORDER_TO_TENANT = 'hmc_external_service_order_notification';
    public const MAP_TENANT_ORDER_TO_PROVIDER = 'hmc_map_tenant_order_to_provider';
    public const MAP_PROVIDER_ORDER_TO_TENANT = 'hmc_map_provider_order_to_tenant';
    public const SEND_TENANT_ORDER_TO_PROVIDER = 'hmc_tenant_order_send_to_provider';
    public const ORDER_NOTIFICATION_EVENT = 'hmc_order_notification_event';

    //product
    public const SYNC_PROVIDER_PRODUCTS_EVENT = 'hmc_sync_provider_products_event';
    public const SYNC_TENANT_PRODUCTS_EVENT = 'hmc_sync_tenant_products_event';

    public const MAP_TENANT_PRODUCT_TO_PROVIDER = 'hmc_map_tenant_product_to_provider';
    public const MAP_PROVIDER_PRODUCT_TO_TENANT = 'hm_map_provider_product_to_tenant';
    public const SEND_PROVIDER_PRODUCT_TO_TENANT = 'hmc_send_provider_product_to_tenant';
    public const SEND_TENANT_PRODUCT_TO_PROVIDER = 'hmc_send_tenant_product_to_provider';


    //menu
    public const SEND_PROVIDER_MENU_TO_TENANT = 'hmc_send_provider_menu_to_tenant';
    public const SEND_TENANT_MENU_TO_PROVIDER = 'hmc_send_tenant_menu_to_provider';
    public const MAP_PROVIDER_MENU_TO_TENANT = 'hmc_map_provider_menu_to_tenant';
    public const MAP_TENANT_MENU_TO_PROVIDER = 'hmc_map_tenant_menu_to_provider';

    private function __construct() { }
}
