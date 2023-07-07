<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EntityManagerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\SerializerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProtocolActionsServiceDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Horeca\MiddlewareCommonLib\DependencyInjection\HorecaApiServiceDI;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class ExternalServiceOrderNotificationMessageHandler implements MessageSubscriberInterface
{
    use LoggerDI;
    use SerializerDI;
    use HorecaApiServiceDI;
    use EntityManagerDI;
    use ProviderApiDI;
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
            'method'         => 'handleExternalServiceOrderNotificationMessage',
            'from_transport' => 'external_service_order_notification'
        ];
    }

    public function handleExternalServiceOrderNotificationMessage(OrderNotificationMessage $message)
    {
        $this->protocolActionsService->handleExternalServiceOrderNotification($message->getOrderNotificationId());

    }
}
