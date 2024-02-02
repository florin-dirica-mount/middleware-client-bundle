<?php

namespace Horeca\MiddlewareClientBundle\Message;

final class MessageTransports
{
    public const SEND_PROVIDER_ORDER_TO_TENANT = 'hmc_external_service_order_notification';
    public const MAP_TENANT_ORDER_TO_PROVIDER = 'hmc_map_tenant_order_to_provider';
    public const SEND_TENANT_ORDER_TO_PROVIDER = 'hmc_tenant_order_send_to_provider';
    public const TENANT_CONFIRM_ORDER_SENT_TO_PROVIDER = 'hmc_tenant_order_confirm_provider_notified';

    private function __construct() { }
}