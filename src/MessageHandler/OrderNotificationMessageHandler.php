<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\OrderNotificationRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\OrderLoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProtocolActionsServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Exception\OrderMappingException;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Horeca\MiddlewareClientBundle\Message\SendProviderOrderToTenantMessage;
use Horeca\MiddlewareClientBundle\Message\SendTenantOrderConfirmationMessage;
use Horeca\MiddlewareClientBundle\Message\MapTenantOrderToProviderMessage;
use Horeca\MiddlewareClientBundle\Message\SendTenantOrderToProviderMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Handles order notifications processing
 */
class OrderNotificationMessageHandler implements MessageSubscriberInterface
{
    use OrderLoggerDI;
    use OrderNotificationRepositoryDI;
    use ProtocolActionsServiceDI;

    protected EntityManagerInterface $entityManager;
    protected LoggerInterface $logger;

    /**
     * @required
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public static function getHandledMessages(): iterable
    {
        yield MapTenantOrderToProviderMessage::class => [
            'method'         => 'handleTenantOrderProcessMappingMessage',
            'from_transport' => MapTenantOrderToProviderMessage::TRANSPORT
        ];

        yield SendTenantOrderToProviderMessage::class => [
            'method'         => 'handleTenantOrderSendToProviderMessage',
            'from_transport' => SendTenantOrderToProviderMessage::TRANSPORT
        ];

        yield SendTenantOrderConfirmationMessage::class => [
            'method'         => 'handleTenantOrderConfirmProviderNotifiedMessage',
            'from_transport' => SendTenantOrderConfirmationMessage::TRANSPORT
        ];

        yield SendProviderOrderToTenantMessage::class => [
            'method'         => 'handleSendProviderOrderToTenantMessage',
            'from_transport' => SendProviderOrderToTenantMessage::TRANSPORT
        ];
    }

    public function handleTenantOrderProcessMappingMessage(OrderNotificationMessage $message): void
    {
        $this->orderLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            $this->protocolActionsService->mapTenantOrderToProviderOrder($notification);
        } catch (\Exception $e) {
            $this->onOrderNotificationException($notification, $e);
        } finally {
            $this->orderLogger->logMemoryUsage();
            $this->orderLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleTenantOrderProcessMappingMessage');
        }
    }

    public function handleTenantOrderSendToProviderMessage(OrderNotificationMessage $message): void
    {
        $this->orderLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            if ($notification->getStatus() !== OrderNotification::STATUS_MAPPED) {
                $this->orderLogger->info(__METHOD__, __LINE__, sprintf('Notification %s status is not *mapped*. Action aborted.', $notification->getId()));

                throw new OrderMappingException('Order is not sent to provider.');
            }

            $this->protocolActionsService->sendTenantOrderToProvider($notification);
        } catch (\Exception $e) {
            $this->onOrderNotificationException($notification, $e);
        } finally {
            $this->orderLogger->logMemoryUsage();
            $this->orderLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleTenantOrderSendToProviderMessage');
        }
    }

    public function handleTenantOrderConfirmProviderNotifiedMessage(OrderNotificationMessage $message): void
    {
        $this->orderLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
            if ($notification->getStatus() !== OrderNotification::STATUS_NOTIFIED) {
                $this->orderLogger->info(__METHOD__, __LINE__, sprintf('Notification %s status is not *mapped*. Action aborted.', $notification->getId()));

                throw new OrderMappingException('Order is not sent to provider.');
            }

            $this->protocolActionsService->confirmTenantOrderProcessed($notification);
        } catch (\Exception $e) {
            $this->onOrderNotificationException($notification, $e);
        } finally {
            $this->orderLogger->logMemoryUsage();
            $this->orderLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleTenantOrderConfirmProviderNotifiedMessage');
        }
    }

    public function handleSendProviderOrderToTenantMessage(OrderNotificationMessage $message): void
    {
        $this->orderLogger->logMemoryUsage();
        $notification = $this->getMessageOrderNotification($message);

        try {
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

        $notification->changeStatus(OrderNotification::STATUS_FAILED);
        $notification->setErrorMessage($e->getMessage());

        $this->entityManager->flush();
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
