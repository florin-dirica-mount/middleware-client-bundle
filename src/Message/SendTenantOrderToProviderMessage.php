<?php

namespace Horeca\MiddlewareClientBundle\Message;

/**
 * Message is dispatched after a Tenant order mapped to the appropriate provider model
 */
class SendTenantOrderToProviderMessage extends MappingNotificationMessage
{
}
