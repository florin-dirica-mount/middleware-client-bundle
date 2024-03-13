<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Horeca\MiddlewareClientBundle\Message\MessageTransports;
use Horeca\MiddlewareClientBundle\Message\Task\SyncProviderProductsMessage;
use Horeca\MiddlewareClientBundle\Message\Task\SyncTenantProductsMessage;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

abstract class TaskMessageHandler implements MessageSubscriberInterface
{

    public static function getHandledMessages(): iterable
    {

        yield SyncTenantProductsMessage::class => [
            'method'         => 'handleSyncTenantProductsMessage',
            'from_transport' => MessageTransports::SYNC_TENANT_PRODUCTS_EVENT
        ];

        yield SyncProviderProductsMessage::class => [
            'method'         => 'handleSyncProviderProductsMessage',
            'from_transport' => MessageTransports::SYNC_PROVIDER_PRODUCTS_EVENT
        ];

    }


    public function handleSyncTenantProductsMessage(SyncTenantProductsMessage $message): void
    {

    }

    public function handleSyncProviderProductsMessage(SyncProviderProductsMessage $message): void
    {

    }

}
