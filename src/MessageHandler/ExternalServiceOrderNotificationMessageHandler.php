<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProtocolActionsServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class ExternalServiceOrderNotificationMessageHandler implements MessageSubscriberInterface
{
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
        yield OrderNotificationMessage::class => [
            'method'         => 'handleExternalServiceOrderNotificationMessage',
            'from_transport' => 'external_service_order_notification'
        ];
    }

    public function handleExternalServiceOrderNotificationMessage(OrderNotificationMessage $message): void
    {
        $notification = $this->entityManager->find(OrderNotification::class, $message->getOrderNotificationId());

        try {
            $this->protocolActionsService->handleExternalServiceOrderNotification($notification);
        } catch (\Exception $e) {
            $this->logger->error('[handleExternalServiceOrderNotification] Exception: ' . $e->getMessage());
            $this->logger->error('[handleExternalServiceOrderNotification] Exception: ' . $e->getTraceAsString());

            $notification->changeStatus(OrderNotification::STATUS_FAILED);
            $notification->setErrorMessage($e->getMessage());

            $this->entityManager->flush();

            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }
    }
}
