<?php

namespace Horeca\MiddlewareClientBundle\MessageHandler;

use Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage;
use Horeca\MiddlewareCommonLib\DependencyInjection\HorecaApiServiceDI;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class OrderNotificationMessageHandler implements MessageSubscriberInterface
{
//    use LoggerDI;
//    use SerializerDI;
//    use HorecaApiServiceDI;
//    use OrderNotificationRepositoryDI;
//    use ProviderApiDI;

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
//        $notification = $this->orderNotificationRepository->find($message->getOrderNotificationId());
//        try {
//            /** @var ShoppingCart $cart */
//            $cart = $this->deserializeJson($notification->getHorecaPayload(), ShoppingCart::class);
//
//            $this->logger->info('[handleOrderNotificationMessage] CartId: ' . $cart->getId());
//
//            /** @var ServiceCredentials $credentials */
//            $credentials = $this->deserializeJson($notification->getServiceCredentials(), ServiceCredentials::class);
//
//            $serviceOrder = $this->providerApi->mapShoppingCartToOrder($cart);
//            $notification->setServicePayload($this->serializeJson($serviceOrder));
//
//            $responseData = $this->providerApi->sendOrder($serviceOrder, $credentials);
//
//            $notification->setResponsePayload($this->serializeJson($responseData));
//            $notification->setServiceOrderId($responseData->data->jobId);
//            $notification->changeStatus(OrderNotification::STATUS_NOTIFIED);
//            $notification->setNotifiedAt(\DateTimeService::now());
//
//            $this->orderNotificationRepository->flush();
//
//            $this->horecaApiService->confirmProviderNotified($cart);
//        } catch (\Exception $e) {
//            $this->logger->critical('[handleOrderNotificationMessage] Exception: ' . $e->getMessage());
//            $this->logger->critical('[handleOrderNotificationMessage] Exception: ' . $e->getTraceAsString());
//
//            $notification->changeStatus(OrderNotification::STATUS_FAILED);
//            $notification->setErrorMessage($e->getMessage());
//
//            $this->orderNotificationRepository->flush();
//        }
    }
}
