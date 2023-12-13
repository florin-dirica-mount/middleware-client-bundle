<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EntityManagerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProtocolActionsServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class OrderNotificationMessageHandler implements MessageSubscriberInterface
{
    use LoggerDI;
    use EntityManagerDI;
    use ProtocolActionsServiceDI;

    private string $transport;

    public function __construct(string $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @inheritDoc
     */
    public static function getHandledMessages(): iterable
    {
        yield OrderNotificationMessage::class => [
            'method'         => 'handleOrderNotificationMessage',
            'from_transport' => 'hmc_order_notification'
        ];
    }

    public function handleOrderNotificationMessage(OrderNotificationMessage $message): void
    {
        $notification = $this->entityManager->find(OrderNotification::class, $message->getOrderNotificationId());

        try {
            $this->protocolActionsService->sendProviderOrderNotification($notification);
        } catch (\Exception $e) {
            $this->logger->error('[handleOrderNotificationMessage] Exception: ' . $e->getMessage());
            $this->logger->error('[handleOrderNotificationMessage] Exception: ' . $e->getTraceAsString());

            $notification->changeStatus(OrderNotification::STATUS_FAILED);
            $notification->setErrorMessage($e->getMessage());

            $this->entityManager->flush();

            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }
    }
}
