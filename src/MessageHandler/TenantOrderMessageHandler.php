<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\OrderLoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProtocolActionsServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Horeca\MiddlewareClientBundle\Message\TenantOrderConfirmProviderNotifiedMessage;
use Horeca\MiddlewareClientBundle\Message\TenantOrderProcessMappingMessage;
use Horeca\MiddlewareClientBundle\Message\TenantOrderSendToProviderMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Handles orders from Tenant to Provider
 */
class TenantOrderMessageHandler implements MessageSubscriberInterface
{
    use OrderLoggerDI;
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
        yield TenantOrderProcessMappingMessage::class => [
            'method'         => 'handleTenantOrderProcessMappingMessage',
            'from_transport' => TenantOrderProcessMappingMessage::TRANSPORT_DEFAULT
        ];

        yield TenantOrderSendToProviderMessage::class => [
            'method'         => 'handleTenantOrderSendToProviderMessage',
            'from_transport' => TenantOrderSendToProviderMessage::TRANSPORT_DEFAULT
        ];

        yield TenantOrderConfirmProviderNotifiedMessage::class => [
            'method'         => 'handleTenantOrderConfirmProviderNotifiedMessage',
            'from_transport' => TenantOrderConfirmProviderNotifiedMessage::TRANSPORT_DEFAULT
        ];
    }

    public function handleTenantOrderProcessMappingMessage(OrderNotificationMessage $message): void
    {
        $this->orderLogger->logMemoryUsage();

        $notification = $this->entityManager->find(OrderNotification::class, $message->getOrderNotificationId());

        try {
            $this->protocolActionsService->sendTenantOrderToProvider($notification);
        } catch (\Exception $e) {
            $this->orderLogger->error(__METHOD__, __LINE__, $e->getMessage());

            $notification->changeStatus(OrderNotification::STATUS_FAILED);
            $notification->setErrorMessage($e->getMessage());

            $this->entityManager->flush();

            throw new UnrecoverableMessageHandlingException($e->getMessage());
        } finally {
            if ($notification) {
                $this->orderLogger->logMemoryUsage();
                $this->orderLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleOrderNotificationMessage');
            }
        }
    }
}
