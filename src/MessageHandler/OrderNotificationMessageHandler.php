<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EntityManagerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\SerializerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use Horeca\MiddlewareCommonLib\DependencyInjection\HorecaApiServiceDI;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class OrderNotificationMessageHandler implements MessageSubscriberInterface
{
    use LoggerDI;
    use SerializerDI;
    use HorecaApiServiceDI;
    use EntityManagerDI;
    use ProviderApiDI;

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

    public function handleOrderNotificationMessage(OrderNotificationMessage $message)
    {
        $notification = $this->entityManager->find(OrderNotification::class, $message->getOrderNotificationId());

        try {
            /** @var ShoppingCart $cart */
            $cart = $this->deserializeJson($notification->getHorecaPayload(), ShoppingCart::class);

            $this->logger->info('[handleOrderNotificationMessage] CartId: ' . $cart->getId());

            /** @var ProviderCredentialsInterface $credentials */
            $credentials = $this->deserializeJson($notification->getServiceCredentials(), $this->providerApi->getProviderCredentialsClass());

            $providerOrder = $this->providerApi->mapShoppingCartToProviderOrder($cart);
            $notification->setServicePayload($this->serializeJson($providerOrder));

            $response = $this->providerApi->saveOrder($providerOrder, $credentials);

            $notification->setResponsePayload($this->serializeJson($response));
            $notification->setServiceOrderId((string) $response->orderId);
            $notification->changeStatus(OrderNotification::STATUS_NOTIFIED);
            $notification->setNotifiedAt(new \DateTime());

            $this->entityManager->flush();

            $this->horecaApiService->confirmProviderNotified($cart);
        } catch (\Exception $e) {
            $this->logger->critical('[handleOrderNotificationMessage] Exception: ' . $e->getMessage());
            $this->logger->critical('[handleOrderNotificationMessage] Exception: ' . $e->getTraceAsString());

            $notification->changeStatus(OrderNotification::STATUS_FAILED);
            $notification->setErrorMessage($e->getMessage());

            $this->entityManager->flush();
        }
    }
}
