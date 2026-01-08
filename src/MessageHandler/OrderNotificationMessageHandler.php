<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EventDispatcherDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\OrderNotificationRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\MappingLoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProtocolActionsServiceDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantApiServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Enum\MappingNotificationEventName;
use Horeca\MiddlewareClientBundle\Enum\MappingNotificationSource;
use Horeca\MiddlewareClientBundle\Enum\MappingNotificationStatus;
use Horeca\MiddlewareClientBundle\Event\ProviderOrderEvent;
use Horeca\MiddlewareClientBundle\Event\TenantOrderEvent;
use Horeca\MiddlewareClientBundle\Exception\OrderMappingException;
use Horeca\MiddlewareClientBundle\Exception\ProviderApiException;
use Horeca\MiddlewareClientBundle\Message\MappingNotificationMessage;
use Horeca\MiddlewareClientBundle\Message\MapProviderOrderToTenantMessage;
use Horeca\MiddlewareClientBundle\Message\MapProviderOrderToTenantSyncMessage;
use Horeca\MiddlewareClientBundle\Message\MapTenantOrderAndSendToProviderMessage;
use Horeca\MiddlewareClientBundle\Message\MapTenantOrderAndSendUpdateToProviderMessage;
use Horeca\MiddlewareClientBundle\Message\MapTenantOrderToProviderMessage;
use Horeca\MiddlewareClientBundle\Message\MapTenantOrderToProviderSyncMessage;
use Horeca\MiddlewareClientBundle\Message\MessageTransports;
use Horeca\MiddlewareClientBundle\Message\MessageTransportsSync;
use Horeca\MiddlewareClientBundle\Message\Order\SendProviderOrderUpdateToTenantMessage;
use Horeca\MiddlewareClientBundle\Message\Order\SendProviderOrderUpdateToTenantSyncMessage;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationEventMessage;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationEventSyncMessage;
use Horeca\MiddlewareClientBundle\Message\SendProviderOrderToTenantMessage;
use Horeca\MiddlewareClientBundle\Message\SendProviderOrderToTenantSyncMessage;
use Horeca\MiddlewareClientBundle\Message\SendTenantOrderToProviderMessage;
use Horeca\MiddlewareClientBundle\Message\SendTenantOrderToProviderSyncMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Handles order notifications processing
 *
 * This handler uses the #[AsMessageHandler] attribute for Symfony 6.1+ compatibility.
 * For Symfony 5.x compatibility, it also implements MessageSubscriberInterface if available.
 *
 * The handler is registered through attributes which work in Symfony 5.4+ when PHP 8 is used.
 */
#[AsMessageHandler(fromTransport: MessageTransports::MAP_TENANT_ORDER_TO_PROVIDER, handles: MapTenantOrderToProviderMessage::class, method: 'handleMapTenantOrderToProviderMessage')]
#[AsMessageHandler(fromTransport: MessageTransportsSync::SYNC, handles: MapTenantOrderToProviderSyncMessage::class, method: 'handleMapTenantOrderToProviderSyncMessage')]
#[AsMessageHandler(fromTransport: MessageTransports::MAP_TENANT_ORDER_AND_SEND_TO_PROVIDER, handles: MapTenantOrderAndSendToProviderMessage::class, method: 'handleMapTenantOrderAndSendToProviderMessage')]
#[AsMessageHandler(fromTransport: MessageTransports::MAP_TENANT_ORDER_AND_SEND_UPDATE_TO_PROVIDER, handles: MapTenantOrderAndSendUpdateToProviderMessage::class, method: 'handleMapTenantOrderAndSendUpdateToProviderMessage')]
#[AsMessageHandler(fromTransport: MessageTransports::MAP_PROVIDER_ORDER_TO_TENANT, handles: MapProviderOrderToTenantMessage::class, method: 'handleMapProviderOrderToTenantMessage')]
#[AsMessageHandler(fromTransport: MessageTransportsSync::SYNC, handles: MapProviderOrderToTenantSyncMessage::class, method: 'handleMapProviderOrderToTenantSyncMessage')]
#[AsMessageHandler(fromTransport: MessageTransports::SEND_TENANT_ORDER_TO_PROVIDER, handles: SendTenantOrderToProviderMessage::class, method: 'handleSendTenantOrderToProviderMessage')]
#[AsMessageHandler(fromTransport: MessageTransportsSync::SYNC, handles: SendTenantOrderToProviderSyncMessage::class, method: 'handleSendTenantOrderToProviderSyncMessage')]
#[AsMessageHandler(fromTransport: MessageTransports::ORDER_NOTIFICATION_EVENT, handles: OrderNotificationEventMessage::class, method: 'handleOrderNotificationEventMessage')]
#[AsMessageHandler(fromTransport: MessageTransportsSync::SYNC, handles: OrderNotificationEventSyncMessage::class, method: 'handleOrderNotificationEventSyncMessage')]
#[AsMessageHandler(fromTransport: MessageTransports::SEND_PROVIDER_ORDER_TO_TENANT, handles: SendProviderOrderToTenantMessage::class, method: 'handleSendProviderOrderToTenantMessage')]
#[AsMessageHandler(fromTransport: MessageTransportsSync::SYNC, handles: SendProviderOrderToTenantSyncMessage::class, method: 'handleSendProviderOrderToTenantSyncMessage')]
#[AsMessageHandler(fromTransport: MessageTransports::SEND_PROVIDER_ORDER_UPDATE_TO_TENANT, handles: SendProviderOrderUpdateToTenantMessage::class, method: 'handleSendProviderOrderUpdateToTenantMessage')]
#[AsMessageHandler(fromTransport: MessageTransportsSync::SYNC, handles: SendProviderOrderUpdateToTenantSyncMessage::class, method: 'handleSendProviderOrderUpdateToTenantSyncMessage')]
class OrderNotificationMessageHandler
{
    use MappingLoggerDI;
    use OrderNotificationRepositoryDI;
    use ProtocolActionsServiceDI;
    use TenantApiServiceDI;
    use EventDispatcherDI;

    public function __construct(protected MessageBusInterface    $messageBus,
                                protected EntityManagerInterface $entityManager)
    {
    }


    /// Map Tenant Order To Provider [START]
    public function handleMapTenantOrderToProviderMessageBase(MappingNotificationMessage $message): OrderNotification
    {
        $this->mappingLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            if ($notification->getSource() !== MappingNotificationSource::Tenant) {
                throw new OrderMappingException('You can map to provider only tenant sourced orders.');
            }
            $notification->changeStatus(MappingNotificationStatus::MappingStarted);
            $this->orderNotificationRepository->save($notification);

            $this->protocolActionsService->mapTenantOrderToProviderOrder($notification);

            if ($notification->getTenant()->isSubscribedToEvent(MappingNotificationEventName::MAPPING_COMPLETED)) {
                $this->messageBus->dispatch(new OrderNotificationEventMessage(MappingNotificationEventName::MAPPING_COMPLETED, $notification));
            }


        } catch (\Throwable $e) {
            $this->onOrderNotificationException($notification, $e);

            if ($notification->getTenant()->isSubscribedToEvent(MappingNotificationEventName::MAPPING_FAILED)) {
                $this->messageBus->dispatch(new OrderNotificationEventMessage(MappingNotificationEventName::MAPPING_FAILED, $notification));
            }
        } finally {
            $this->mappingLogger->logMemoryUsage();
            $this->mappingLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleMapTenantOrderToProviderMessage');
        }
        return $notification;
    }

    public function handleMapTenantOrderToProviderMessage(MappingNotificationMessage $message): void
    {
        $this->handleMapTenantOrderToProviderMessageBase($message);
    }

    public function handleMapTenantOrderToProviderSyncMessage(MappingNotificationMessage $message): void
    {
        $this->handleMapTenantOrderToProviderMessageBase($message);
    }

    public function handleMapTenantOrderAndSendToProviderMessage(MappingNotificationMessage $message): void
    {
        $notification = $this->handleMapTenantOrderToProviderMessageBase($message);

        $this->eventDispatcher->dispatch(new TenantOrderEvent($notification), TenantOrderEvent::ORDER_MAPPED);

        $this->messageBus->dispatch(new SendTenantOrderToProviderMessage($notification));

    }

    public function handleMapTenantOrderAndSendUpdateToProviderMessage(MappingNotificationMessage $message): void
    {
        $notification = $this->handleMapTenantOrderToProviderMessageBase($message);

        $this->eventDispatcher->dispatch(new TenantOrderEvent($notification), TenantOrderEvent::ORDER_UPDATE_MAPPED);

        $this->messageBus->dispatch(new SendTenantOrderToProviderMessage($notification));

    }
    /// Map Tenant Order To Provider [END]


    /// Map Provider Order To Tenant [START]
    public function handleMapProviderOrderToTenantMessageBase(MappingNotificationMessage $message, ?bool $sync = false): void
    {
        $this->mappingLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            if ($notification->getSource() !== MappingNotificationSource::Provider) {
                throw new OrderMappingException('You can map to tenant only provider sourced orders.');
            }

            $notification->changeStatus(MappingNotificationStatus::MappingStarted);
            $this->orderNotificationRepository->save($notification);

            $this->protocolActionsService->mapProviderOrderToTenantOrder($notification);
            //todo send event to provider if subscribed

//            if ($notification->getTenant()->isSubscribedToEvent(MappingNotificationEventName::MAPPING_COMPLETED)) {
//                $this->messageBus->dispatch(new OrderNotificationEventMessage(MappingNotificationEventName::MAPPING_COMPLETED, $notification));
//            }
            if (!$sync) {
                $this->messageBus->dispatch(new SendProviderOrderToTenantMessage($notification));
            }
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

    public function handleMapProviderOrderToTenantMessage(MappingNotificationMessage $message): void
    {
        $this->handleMapProviderOrderToTenantMessageBase($message);
    }

    public function handleMapProviderOrderToTenantSyncMessage(MappingNotificationMessage $message): void
    {
        $this->handleMapProviderOrderToTenantMessageBase($message, true);
    }
    /// Map Provider Order To Tenant [END]


    /// Send Tenant Order To Provider [START]
    public function handleSendTenantOrderToProviderMessageBase(MappingNotificationMessage $message, ?bool $sync = false): void
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
                if (!$sync) {
                    $this->messageBus->dispatch(new OrderNotificationEventMessage(MappingNotificationEventName::PROVIDER_NOTIFIED, $notification));
                }
            }
        } catch (\Throwable $e) {
            $this->onOrderNotificationException($notification, $e);

            if ($notification->getTenant()->isSubscribedToEvent(MappingNotificationEventName::PROVIDER_NOTIFICATION_FAILED)) {
                $this->messageBus->dispatch(new OrderNotificationEventMessage(MappingNotificationEventName::PROVIDER_NOTIFICATION_FAILED, $notification, 'handleSendTenantOrderToProviderMessageBase'));
            }
        } finally {
            $this->mappingLogger->logMemoryUsage();
            $this->mappingLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleSendTenantOrderToProviderMessage');
        }
    }

    public function handleSendTenantOrderToProviderMessage(MappingNotificationMessage $message): void
    {
        $this->handleSendTenantOrderToProviderMessageBase($message);
    }

    public function handleSendTenantOrderToProviderSyncMessage(MappingNotificationMessage $message): void
    {
        $this->handleSendTenantOrderToProviderMessageBase($message, true);
    }
    /// Send Tenant Order To Provider [END]


    /// Send Order Notification Event [START]
    public function handleOrderNotificationEventMessageBase(OrderNotificationEventMessage $message, ?bool $sync = false): void
    {
        $this->mappingLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            if ($notification->getSource() === MappingNotificationSource::Tenant && $notification->getTenant()->isSubscribedToEvent($message->getEvent())) {
                $this->tenantApiService->sendOrderNotificationEvent($message->getEvent(), $notification);
            }

            if ($notification->getStatus() !== MappingNotificationStatus::Confirmed && in_array($message->getEvent(), [
                    MappingNotificationEventName::PROVIDER_NOTIFIED,
                    MappingNotificationEventName::TENANT_NOTIFIED
                ])) {
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

    public function handleOrderNotificationEventMessage(OrderNotificationEventMessage $message): void
    {
        $this->handleOrderNotificationEventMessageBase($message);
    }

    public function handleOrderNotificationEventSyncMessage(OrderNotificationEventMessage $message): void
    {
        $this->handleOrderNotificationEventMessageBase($message, true);
    }
    /// Send Order Notification Event [END]


    /// Send Provider Order To Tenant [START]
    public function handleSendProviderOrderToTenantMessageBase(MappingNotificationMessage $message, ?bool $sync = false): void
    {
        $this->mappingLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            $notification->changeStatus(MappingNotificationStatus::SendingNotification);
            $this->orderNotificationRepository->save($notification);

            $response = $this->protocolActionsService->sendProviderOrderToTenant($notification);

            if ($notification->getTenant()->isSubscribedToEvent(MappingNotificationEventName::TENANT_NOTIFIED)) {
                $this->messageBus->dispatch(new OrderNotificationEventMessage(MappingNotificationEventName::TENANT_NOTIFIED, $notification));
            }
            $this->eventDispatcher->dispatch(new ProviderOrderEvent($notification), ProviderOrderEvent::TENANT_NOTIFIED);

        } catch (\Throwable $e) {
            $this->onOrderNotificationException($notification, $e);
        } finally {
            $this->mappingLogger->logMemoryUsage();
            $this->mappingLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleSendProviderOrderToTenantMessage');
        }
    }

    public function handleSendProviderOrderToTenantMessage(MappingNotificationMessage $message): void
    {
        $this->handleSendProviderOrderToTenantMessageBase($message);
    }

    public function handleSendProviderOrderToTenantSyncMessage(MappingNotificationMessage $message): void
    {
        $this->handleSendProviderOrderToTenantMessageBase($message, true);
    }
    /// Send Provider Order To Tenant [END]


    /// Send Provider Order To Tenant [START]
    public function handleSendProviderOrderUpdateToTenantMessageBase(MappingNotificationMessage $message, ?bool $sync = false): void
    {
        $this->mappingLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            $notification->changeStatus(MappingNotificationStatus::SendingNotification);
            $this->orderNotificationRepository->save($notification);

            $this->protocolActionsService->sendProviderOrderUpdateToTenant($notification);

            $this->eventDispatcher->dispatch(new ProviderOrderEvent($notification), ProviderOrderEvent::TENANT_NOTIFIED);

        } catch (\Throwable $e) {
            $this->onOrderNotificationException($notification, $e);
        } finally {
            $this->mappingLogger->logMemoryUsage();
            $this->mappingLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleSendProviderOrderUpdateToTenantMessageBase');
        }
    }

    public function handleSendProviderOrderUpdateToTenantMessage(MappingNotificationMessage $message): void
    {
        $this->handleSendProviderOrderUpdateToTenantMessageBase($message);
    }

    public function handleSendProviderOrderUpdateToTenantSyncMessage(MappingNotificationMessage $message): void
    {
        $this->handleSendProviderOrderUpdateToTenantMessageBase($message, true);
    }

    /// Send Provider Order To Tenant [END]


    protected function onOrderNotificationException(OrderNotification $notification, \Throwable $e): void
    {
        $this->mappingLogger->error(__METHOD__, __LINE__, $e->getMessage());
        $this->mappingLogger->debug(__METHOD__, __LINE__, $e->getTraceAsString());

        $notification->changeStatus(MappingNotificationStatus::Failed);
        $notification->setErrorMessage($e->getMessage());

        if ($e instanceof ProviderApiException) {
            $notification->setResponsePayloadString($e->getResponseContent());
        }

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

