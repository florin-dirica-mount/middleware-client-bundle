<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EntityManagerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\SerializerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\VO\Provider\BaseProviderOrderResponse;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderOrderInterface;
use Horeca\MiddlewareCommonLib\DependencyInjection\HorecaApiServiceDI;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;

class ProtocolActionsService
{
    use EntityManagerDI;
    use ProviderApiDI;
    use HorecaApiServiceDI;
    use SerializerDI;
    use LoggerDI;

    public function handleExternalServiceOrderNotification($orderNotificationId): ?SendShoppingCartResponse
    {

        $notification = $this->entityManager->find(OrderNotification::class, $orderNotificationId);
        try {
            if(!$notification->getServicePayload() || !$notification->getRestaurantId()){
                $this->logger->warning('[handleExternalServiceOrderNotification] missing ServicePayload or RestaurantId. Action aborted for notification: ' . $notification->getId());

                $notification->changeStatus(OrderNotification::STATUS_FAILED);
                $notification->setErrorMessage('Missing ServicePayload. Action aborted');

                $this->entityManager->flush();
                return null;
            }

            /** @var ProviderOrderInterface $order */
            $order = $this->deserializeJson($notification->getServicePayload(), $this->providerApi->getProviderOrderClass());


            $shoppingCart = $this->providerApi->mapProviderOrderToShoppingCart($order);

            $notification->setHorecaPayload($this->serializeJson($shoppingCart));

            $response = $this->horecaApiService->sendShoppingCart($shoppingCart, $notification->getRestaurantId());

            $notification->setResponsePayload($this->serializeJson($response));
            $notification->setHorecaOrderId((string)$response->horecaOrderId);
            $notification->changeStatus(OrderNotification::STATUS_NOTIFIED);
            $notification->setNotifiedAt(new \DateTime());

            $this->entityManager->flush();

            return $response;

        } catch (\Exception $e) {
            $this->logger->critical('[handleExternalServiceOrderNotification] Exception: ' . $e->getMessage());
            $this->logger->critical('[handleExternalServiceOrderNotification] Exception: ' . $e->getTraceAsString());

            $notification->changeStatus(OrderNotification::STATUS_FAILED);
            $notification->setErrorMessage($e->getMessage());

            $this->entityManager->flush();
            return null;
        }
    }

    public function handleHorecaOrderNotification($orderNotificationId): ?BaseProviderOrderResponse
    {
        $notification = $this->entityManager->find(OrderNotification::class, $orderNotificationId);

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
            $notification->setServiceOrderId((string)$response->orderId);
            $notification->changeStatus(OrderNotification::STATUS_NOTIFIED);
            $notification->setNotifiedAt(new \DateTime());

            $this->entityManager->flush();

            $this->horecaApiService->confirmProviderNotified($cart);

            return $response;

        } catch (\Exception $e) {
            $this->logger->critical('[handleOrderNotificationMessage] Exception: ' . $e->getMessage());
            $this->logger->critical('[handleOrderNotificationMessage] Exception: ' . $e->getTraceAsString());

            $notification->changeStatus(OrderNotification::STATUS_FAILED);
            $notification->setErrorMessage($e->getMessage());

            $this->entityManager->flush();
            return null;

        }
    }

}
