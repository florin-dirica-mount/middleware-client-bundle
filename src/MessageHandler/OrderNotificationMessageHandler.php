<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\OrderNotificationRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\OrderLoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProtocolActionsServiceDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantApiServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Enum\OrderNotificationEventName;
use Horeca\MiddlewareClientBundle\Enum\OrderNotificationSource;
use Horeca\MiddlewareClientBundle\Enum\OrderNotificationStatus;
use Horeca\MiddlewareClientBundle\Exception\OrderMappingException;
use Horeca\MiddlewareClientBundle\Message\MapTenantOrderToProviderMessage;
use Horeca\MiddlewareClientBundle\Message\MessageTransports;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationEventMessage;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Horeca\MiddlewareClientBundle\Message\SendProviderOrderToTenantMessage;
use Horeca\MiddlewareClientBundle\Message\SendTenantOrderToProviderMessage;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Handles order notifications processing
 */
class OrderNotificationMessageHandler implements MessageSubscriberInterface
{
    use OrderLoggerDI;
    use OrderNotificationRepositoryDI;
    use ProtocolActionsServiceDI;
    use TenantApiServiceDI;

    public function __construct(protected MessageBusInterface $messageBus) { }

    /**
     * @inheritDoc
     */
    public static function getHandledMessages(): iterable
    {
        yield MapTenantOrderToProviderMessage::class => [
            'method'         => 'handleMapTenantOrderToProviderMessage',
            'from_transport' => MessageTransports::MAP_TENANT_ORDER_TO_PROVIDER
        ];

        yield SendTenantOrderToProviderMessage::class => [
            'method'         => 'handleSendTenantOrderToProviderMessage',
            'from_transport' => MessageTransports::SEND_TENANT_ORDER_TO_PROVIDER
        ];

        yield OrderNotificationEventMessage::class => [
            'method'         => 'handleOrderNotificationEventMessage',
            'from_transport' => MessageTransports::ORDER_NOTIFICATION_EVENT
        ];

        yield SendProviderOrderToTenantMessage::class => [
            'method'         => 'handleSendProviderOrderToTenantMessage',
            'from_transport' => MessageTransports::SEND_PROVIDER_ORDER_TO_TENANT
        ];
    }

    public function handleMapTenantOrderToProviderMessage(OrderNotificationMessage $message): void
    {
        $this->orderLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            $notification->changeStatus(OrderNotificationStatus::MappingStarted);
            $this->orderNotificationRepository->save($notification);

            $this->protocolActionsService->mapTenantOrderToProviderOrder($notification);
        } catch (\Exception $e) {
            $this->onOrderNotificationException($notification, $e);

            $this->messageBus->dispatch(new OrderNotificationEventMessage(OrderNotificationEventName::MAPPING_FAILED, $notification));
        } finally {
            $this->orderLogger->logMemoryUsage();
            $this->orderLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleMapTenantOrderToProviderMessage');
        }
    }

    public function handleSendTenantOrderToProviderMessage(OrderNotificationMessage $message): void
    {
        $this->orderLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            if ($notification->getStatus() !== OrderNotificationStatus::Mapped) {
                $this->orderLogger->info(__METHOD__, __LINE__, sprintf('Notification %s status is not *mapped*. Action aborted.', $notification->getId()));

                throw new OrderMappingException('Order is not sent to provider.');
            }

            $notification->changeStatus(OrderNotificationStatus::SendingNotification);
            $this->orderNotificationRepository->save($notification);

            $this->protocolActionsService->sendTenantOrderToProvider($notification);
        } catch (\Exception $e) {
            $this->onOrderNotificationException($notification, $e);

            $this->messageBus->dispatch(new OrderNotificationEventMessage(OrderNotificationEventName::MAPPING_FAILED, $notification));
        } finally {
            $this->orderLogger->logMemoryUsage();
            $this->orderLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleSendTenantOrderToProviderMessage');
        }
    }

    public function handleOrderNotificationEventMessage(OrderNotificationEventMessage $message): void
    {
        $this->orderLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            if ($notification->getSource() === OrderNotificationSource::Tenant) {
                $this->tenantApiService->sendOrderNotificationEvent($message->getEvent(), $notification);
            }

            // todo: send event to provider if needed, for the other order source
        } catch (\Exception $e) {
            $this->onOrderNotificationException($notification, $e);
        } finally {
            $this->orderLogger->logMemoryUsage();
            $this->orderLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleOrderNotificationEventMessage');
        }
    }

    public function handleSendProviderOrderToTenantMessage(OrderNotificationMessage $message): void
    {
        $this->orderLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            $notification->changeStatus(OrderNotificationStatus::SendingNotification);
            $this->orderNotificationRepository->save($notification);

            $this->protocolActionsService->sendProviderOrderToTenant($notification);
        } catch (\Exception $e) {
            $this->onOrderNotificationException($notification, $e);
        } finally {
            $this->orderLogger->logMemoryUsage();
            $this->orderLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleSendProviderOrderToTenantMessage');
        }
    }

    protected function onOrderNotificationException(OrderNotification $notification, \Exception $e): void
    {
        $this->orderLogger->error(__METHOD__, __LINE__, $e->getMessage());

        $notification->changeStatus(OrderNotificationStatus::Failed);
        $notification->setErrorMessage($e->getMessage());

        $this->orderNotificationRepository->save($notification);
    }

    protected function getMessageOrderNotification(OrderNotificationMessage $message): OrderNotification
    {
        $notification = $this->orderNotificationRepository->find($message->getOrderNotificationId());

        if (!$notification) {
            throw new UnrecoverableMessageHandlingException(sprintf('OrderNotification with id %s not found', $message->getOrderNotificationId()));
        }

        return $notification;
    }
}
