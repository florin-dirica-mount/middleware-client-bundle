<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EntityManagerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\SerializerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\TenantRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantApiServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\VO\Provider\BaseProviderOrderResponse;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderOrderInterface;
use Horeca\MiddlewareCommonLib\Exception\HorecaException;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProtocolActionsService
{
    use EntityManagerDI;
    use ProviderApiDI;
    use SerializerDI;
    use LoggerDI;
    use TenantRepositoryDI;
    use TenantApiServiceDI;

    private string $providerCredentialsClass;

    public function __construct(ContainerInterface $container)
    {
        $this->providerCredentialsClass = (string) $container->getParameter('horeca.provider_credentials_class');
    }

    /**
     * @throws AccessDeniedException
     */
    public function authorizeTenant(Request $request): Tenant
    {
        if ($auth = $request->headers->get('Authorization')) {
            $credentials = base64_decode(substr($auth, 6));
            list($id, $apiKey) = explode(':', $credentials);

            $tenant = $this->tenantRepository->findOneByApiKeyAndId($apiKey, $id);
        } elseif ($auth = $request->headers->get('Api-Key')) {
            $tenant = $this->tenantRepository->findOneByApiKey((string) $auth);
        } else {
            $tenant = null;
        }

        if (!$tenant) {
            throw new AccessDeniedException('Invalid credentials');
        }

        return $tenant;
    }

    /**
     * @throws HorecaException
     */
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
        $shoppingCart = $this->providerApi->mapProviderOrderToShoppingCart($notification->getTenant(), $order);

        $notification->setHorecaPayload($this->serializeJson($shoppingCart));

        $response = $this->tenantApiService->sendShoppingCart($notification->getTenant(), $shoppingCart, $notification->getRestaurantId());

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
            $credentials = $this->tenantRepository->findTenantCredentials($notification->getTenant(), $this->providerCredentialsClass);
        } elseif ($notification->getServiceCredentials()) {
            /** @var ProviderCredentialsInterface $credentials */
            $credentials = $this->deserializeJson($notification->getServiceCredentials(), $this->providerApi->getProviderCredentialsClass());
        } else {
            $credentials = null;
        }

        if (!$credentials) {
            throw new \Exception('Missing parameters: provider credentials!');
        }

        $providerOrder = $this->providerApi->mapShoppingCartToProviderOrder($notification->getTenant(), $cart);
        $notification->setServicePayload($this->serializeJson($providerOrder));

        $response = $this->providerApi->saveOrder($providerOrder, $credentials);

        $notification->setResponsePayload($this->serializeJson($response));
        $notification->setServiceOrderId((string) $response->orderId);
        $notification->changeStatus(OrderNotification::STATUS_NOTIFIED);
        $notification->setNotifiedAt(new \DateTime());

        $this->entityManager->flush();

        $this->tenantApiService->confirmProviderNotified($notification);

        return $response;
    }

}
