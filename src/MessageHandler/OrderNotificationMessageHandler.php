<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\OrderNotificationRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\MappingLoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProtocolActionsServiceDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantApiServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Enum\MappingNotificationStatus;
use Horeca\MiddlewareClientBundle\Enum\MappingNotificationEventName;
use Horeca\MiddlewareClientBundle\Enum\MappingNotificationSource;
use Horeca\MiddlewareClientBundle\Exception\OrderMappingException;
use Horeca\MiddlewareClientBundle\Message\MappingNotificationMessage;
use Horeca\MiddlewareClientBundle\Message\MapProviderOrderToTenantMessage;
use Horeca\MiddlewareClientBundle\Message\MapTenantOrderToProviderMessage;
use Horeca\MiddlewareClientBundle\Message\MessageTransports;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationEventMessage;
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
    use MappingLoggerDI;
    use OrderNotificationRepositoryDI;
    use ProtocolActionsServiceDI;
    use TenantApiServiceDI;

    public function __construct(protected MessageBusInterface    $messageBus,
                                protected EntityManagerInterface $entityManager)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getHandledMessages(): iterable
    {
        yield MapTenantOrderToProviderMessage::class => [
            'method'         => 'handleMapTenantOrderToProviderMessage',
            'from_transport' => MessageTransports::MAP_TENANT_ORDER_TO_PROVIDER
        ];
        yield MapProviderOrderToTenantMessage::class => [
            'method'         => 'handleMapProviderOrderToTenantMessage',
            'from_transport' => MessageTransports::MAP_PROVIDER_ORDER_TO_TENANT
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

    public function handleMapTenantOrderToProviderMessage(MappingNotificationMessage $message): void
    {
        $this->mappingLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            $notification->changeStatus(MappingNotificationStatus::MappingStarted);
            $this->orderNotificationRepository->save($notification);

            $this->protocolActionsService->mapTenantOrderToProviderOrder($notification);

            if ($notification->getTenant()->isSubscribedToEvent(MappingNotificationEventName::MAPPING_COMPLETED)) {
                $this->messageBus->dispatch(new OrderNotificationEventMessage(MappingNotificationEventName::MAPPING_COMPLETED, $notification));
            }

            $this->messageBus->dispatch(new SendTenantOrderToProviderMessage($notification));
        } catch (\Throwable $e) {
            $this->onOrderNotificationException($notification, $e);

            if ($notification->getTenant()->isSubscribedToEvent(MappingNotificationEventName::MAPPING_FAILED)) {
                $this->messageBus->dispatch(new OrderNotificationEventMessage(MappingNotificationEventName::MAPPING_FAILED, $notification));
            }
        } finally {
            $this->mappingLogger->logMemoryUsage();
            $this->mappingLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleMapTenantOrderToProviderMessage');
        }
    }
    public function handleMapProviderOrderToTenantMessage(MappingNotificationMessage $message): void
    {
        $this->mappingLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            $notification->changeStatus(MappingNotificationStatus::MappingStarted);
            $this->orderNotificationRepository->save($notification);

            $this->protocolActionsService->mapProviderOrderToTenantOrder($notification);
            //todo send event to provider if subscribed

//            if ($notification->getTenant()->isSubscribedToEvent(MappingNotificationEventName::MAPPING_COMPLETED)) {
//                $this->messageBus->dispatch(new OrderNotificationEventMessage(MappingNotificationEventName::MAPPING_COMPLETED, $notification));
//            }

            $this->messageBus->dispatch(new SendProviderOrderToTenantMessage($notification));
        } catch (\Throwable $e) {
            $this->onOrderNotificationException($notification, $e);
            //todo send event to provider if subscribed

//            if ($notification->getTenant()->isSubscribedToEvent(MappingNotificationEventName::MAPPING_FAILED)) {
//                $this->messageBus->dispatch(new OrderNotificationEventMessage(MappingNotificationEventName::MAPPING_FAILED, $notification));
//            }
        } finally {
            $this->mappingLogger->logMemoryUsage();
            $this->mappingLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleMapProviderOrderToTenantMessage');
        }
    }

    public function handleSendTenantOrderToProviderMessage(MappingNotificationMessage $message): void
    {
        $this->mappingLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            if (!$notification->hasStatus(MappingNotificationStatus::Mapped) || empty($notification->getProviderPayload())) {
                $this->mappingLogger->info(__METHOD__, __LINE__, sprintf('Notification %s status is not *mapped*. Action aborted.', $notification->getId()));

                throw new OrderMappingException('Order is not sent to provider.');
            }

            if ($notification->getStatus() !== MappingNotificationStatus::SendingNotification) {
                $notification->changeStatus(MappingNotificationStatus::SendingNotification);
                $this->orderNotificationRepository->save($notification);
            }

            $this->mappingLogger->info(__METHOD__, __LINE__, 'Sending order to provider...');
            $this->protocolActionsService->sendTenantOrderToProvider($notification);
            $this->mappingLogger->info(__METHOD__, __LINE__, sprintf('Order %s sent to provider with id %s', $notification->getId(), $notification->getProviderObjectId()));

            if ($notification->getTenant()->isSubscribedToEvent(MappingNotificationEventName::PROVIDER_NOTIFIED)) {
                $this->messageBus->dispatch(new OrderNotificationEventMessage(MappingNotificationEventName::PROVIDER_NOTIFIED, $notification));
            }
        } catch (\Throwable $e) {
            $this->onOrderNotificationException($notification, $e);

            if ($notification->getTenant()->isSubscribedToEvent(MappingNotificationEventName::PROVIDER_NOTIFICATION_FAILED)) {
                $this->messageBus->dispatch(new OrderNotificationEventMessage(MappingNotificationEventName::PROVIDER_NOTIFICATION_FAILED, $notification));
            }
        } finally {
            $this->mappingLogger->logMemoryUsage();
            $this->mappingLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleSendTenantOrderToProviderMessage');
        }
    }

    public function handleOrderNotificationEventMessage(OrderNotificationEventMessage $message): void
    {
        $this->mappingLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            if ($notification->getSource() === MappingNotificationSource::Tenant && $notification->getTenant()->isSubscribedToEvent($message->getEvent())) {
                $this->tenantApiService->sendOrderNotificationEvent($message->getEvent(), $notification);
            }

            if ($notification->getStatus() !== MappingNotificationStatus::Confirmed
                && in_array($message->getEvent(), [MappingNotificationEventName::PROVIDER_NOTIFIED, MappingNotificationEventName::TENANT_NOTIFIED])) {
                $notification->changeStatus(MappingNotificationStatus::Confirmed);
                $this->entityManager->flush();
            }

            // todo: send event to provider if needed, for the other order source
        } catch (\Throwable $e) {
            $this->onOrderNotificationException($notification, $e);
        } finally {
            $this->mappingLogger->logMemoryUsage();
            $this->mappingLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleOrderNotificationEventMessage');
        }
    }

    public function handleSendProviderOrderToTenantMessage(MappingNotificationMessage $message): void
    {
        $this->mappingLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            $notification->changeStatus(MappingNotificationStatus::SendingNotification);
            $this->orderNotificationRepository->save($notification);

            $this->protocolActionsService->sendProviderOrderToTenant($notification);
        } catch (\Throwable $e) {
            $this->onOrderNotificationException($notification, $e);
        } finally {
            $this->mappingLogger->logMemoryUsage();
            $this->mappingLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleSendProviderOrderToTenantMessage');
        }
    }

    protected function onOrderNotificationException(OrderNotification $notification, \Throwable $e): void
    {
        $this->mappingLogger->error(__METHOD__, __LINE__, $e->getMessage());

        $notification->changeStatus(MappingNotificationStatus::Failed);
        $notification->setErrorMessage($e->getMessage());

        $this->orderNotificationRepository->save($notification);
    }

    protected function getMessageOrderNotification(MappingNotificationMessage $message): OrderNotification
    {
        $notification = $this->orderNotificationRepository->find($message->getNotificationId());

        if (!$notification) {
            throw new UnrecoverableMessageHandlingException(sprintf('OrderNotification with id %s not found', $message->getNotificationId()));
        }

        return $notification;
    }
}
