<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Horeca\MiddlewareClientBundle\Service\OrderLogger;
use Horeca\MiddlewareClientBundle\Service\ProtocolActionsService;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class OrderNotificationMessageHandler implements MessageSubscriberInterface
{
    private OrderLogger $orderLogger;
    private ProtocolActionsService $protocolActionsService;
    private EntityManagerInterface $entityManager;

    public function __construct(OrderLogger            $orderLogger,
                                ProtocolActionsService $protocolActionsService,
                                EntityManagerInterface $entityManager)
    {
        $this->orderLogger = $orderLogger;
        $this->protocolActionsService = $protocolActionsService;
        $this->entityManager = $entityManager;
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
        if (!$notification) {
            return;
        }

        try {
            $this->protocolActionsService->sendProviderOrderNotification($notification);

            $this->orderLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleOrderNotificationMessage');
        } catch (\Exception $e) {
            $this->orderLogger->error(__METHOD__, __LINE__, $e->getMessage());

            $notification->changeStatus(OrderNotification::STATUS_FAILED);
            $notification->setErrorMessage($e->getMessage());

            $this->entityManager->flush();

            $this->orderLogger->saveTo($notification, 'OrderNotificationMessageHandler::handleOrderNotificationMessage');

            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }
    }
}
