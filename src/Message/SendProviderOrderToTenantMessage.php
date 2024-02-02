<?php

namespace Horeca\MiddlewareClientBundle\Message;

class SendProviderOrderToTenantMessage extends OrderNotificationMessage
{
    const TRANSPORT = 'hmc_external_service_order_notification';
}