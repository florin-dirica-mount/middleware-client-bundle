<?php

namespace Horeca\MiddlewareClientBundle\Message;

/**
 * Message is dispatched when a Tenant order is received
 */
class MapTenantOrderToProviderMessage extends OrderNotificationMessage
{
    const TRANSPORT = 'hmc_tenant_order_process_mapping';
}