<?php

namespace Horeca\MiddlewareClientBundle\Message;

/**
 * Message is dispatched after a Tenant order is sent to the provider
 */
class TenantOrderConfirmProviderNotifiedMessage
{
    const TRANSPORT_DEFAULT = 'hmc_tenant_order_confirm_provider_notified';

}