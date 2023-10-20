<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EntityManagerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\SerializerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\TenantRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\VO\Provider\BaseProviderOrderResponse;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderOrderInterface;
use Horeca\MiddlewareCommonLib\DependencyInjection\HorecaApiServiceDI;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProtocolActionsService
{
    use EntityManagerDI;
    use ProviderApiDI;
    use HorecaApiServiceDI;
    use SerializerDI;
    use LoggerDI;
    use TenantRepositoryDI;

    private string $tenantCredentialsClass;

    public function __construct(ContainerInterface $container)
    {
        $this->tenantCredentialsClass = (string) $container->getParameter('horeca.tenant_credentials_class');
    }

    public function handleExternalServiceOrderNotification(OrderNotification $notification): ?SendShoppingCartResponse
    {
        if (!$notification->getServicePayload() || !$notification->getRestaurantId()) {
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
        $notification->setHorecaOrderId($response->horecaOrderId);
        $notification->changeStatus(OrderNotification::STATUS_NOTIFIED);
        $notification->setNotifiedAt(new \DateTime());

        $this->entityManager->flush();

        return $response;
    }

    /**
     * @throws \Exception
     */
    public function sendProviderOrderNotification(OrderNotification $notification): ?BaseProviderOrderResponse
    {
        /** @var ShoppingCart $cart */
        $cart = $this->deserializeJson($notification->getHorecaPayload(), ShoppingCart::class);

        $this->logger->info('[handleOrderNotificationMessage] CartId: ' . $cart->getId());

        if ($notification->getTenant() && !$notification->getServiceCredentials()) {
            $credentials = $this->tenantRepository->findTenantCredentials($notification->getTenant(), $this->tenantCredentialsClass);
        } elseif ($notification->getServiceCredentials()) {
            /** @var ProviderCredentialsInterface $credentials */
            $credentials = $this->deserializeJson($notification->getServiceCredentials(), $this->providerApi->getProviderCredentialsClass());
        } else {
            $credentials = null;
        }

        if (!$credentials) {
            throw new \Exception('Missing parameters: provider credentials!');
        }

        $providerOrder = $this->providerApi->mapShoppingCartToProviderOrder($cart);
        $notification->setServicePayload($this->serializeJson($providerOrder));

        $response = $this->providerApi->saveOrder($providerOrder, $credentials);

        $notification->setResponsePayload($this->serializeJson($response));
        $notification->setServiceOrderId((string) $response->orderId);
        $notification->changeStatus(OrderNotification::STATUS_NOTIFIED);
        $notification->setNotifiedAt(new \DateTime());

        $this->entityManager->flush();

        $this->horecaApiService->confirmProviderNotified($cart);

        return $response;
    }

}
