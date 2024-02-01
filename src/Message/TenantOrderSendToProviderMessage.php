<?php

namespace Horeca\MiddlewareClientBundle\Message;

/**
 * Message is dispatched after a Tenant order mapped to the appropriate provider model
 */
class TenantOrderSendToProviderMessage
{
    const TRANSPORT_DEFAULT = 'hmc_tenant_order_send_to_provider';

}