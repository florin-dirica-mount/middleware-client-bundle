<?php

namespace Horeca\MiddlewareClientBundle\Message;

/**
 * Message is dispatched after a Tenant order is sent to the provider
 */
class SendTenantOrderConfirmationMessage extends OrderNotificationMessage
{
    const TRANSPORT = 'hmc_tenant_order_confirm_provider_notified';
}