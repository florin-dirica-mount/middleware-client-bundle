<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\OrderLoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProtocolActionsServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class OrderNotificationMessageHandler implements MessageSubscriberInterface
{
    private static ?string $transport = null;

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

    public function __construct(?string $transport)
    {
        self::$transport = $transport;
    }

    /**
     * @inheritDoc
     */
    public static function getHandledMessages(): iterable
    {
        yield OrderNotificationMessage::class => [
            'method'         => 'handleOrderNotificationMessage',
            'from_transport' => self::$transport ?: OrderNotificationMessage::TRANSPORT_DEFAULT
        ];
    }

    public function handleOrderNotificationMessage(OrderNotificationMessage $message): void
    {
        $this->orderLogger->logMemoryUsage();

        $notification = $this->entityManager->find(OrderNotification::class, $message->getOrderNotificationId());

        try {
            $this->protocolActionsService->sendProviderOrderNotification($notification);
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
