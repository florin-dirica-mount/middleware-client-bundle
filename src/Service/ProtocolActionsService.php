<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
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
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class ProtocolActionsService
{
    use ProviderApiDI;
    use TenantRepositoryDI;
    use TenantApiServiceDI;

    protected EntityManagerInterface $entityManager;
    protected LoggerInterface $logger;
    protected SerializerInterface $serializer;

    /**
     * @required
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

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

    private string $providerCredentialsClass;

    public function __construct(string $providerCredentialsClass)
    {
        $this->providerCredentialsClass = $providerCredentialsClass;
    }

    /**
     * @throws HorecaException
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
            throw new HorecaException('Invalid credentials');
        }

        return $tenant;
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
        $order = $this->serializer->deserialize($notification->getServicePayload(), $this->providerApi->getProviderOrderClass(), 'json');
        $shoppingCart = $this->providerApi->mapProviderOrderToShoppingCart($notification->getTenant(), $order);

        $notification->setHorecaPayload($this->serializer->serialize($shoppingCart, 'json'));

        $response = $this->tenantApiService->sendShoppingCart($notification->getTenant(), $shoppingCart, $notification->getRestaurantId());

        $notification->setResponsePayload($this->serializer->serialize($response, 'json'));
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
        $cart = $this->serializer->deserialize($notification->getHorecaPayload(), ShoppingCart::class, 'json');

        $this->logger->info('[handleOrderNotificationMessage] CartId: ' . $cart->getId());

        if ($notification->getTenant() && !$notification->getServiceCredentials()) {
            $credentials = $this->tenantRepository->findTenantCredentials($notification->getTenant(), $this->providerCredentialsClass);
        } elseif ($notification->getServiceCredentials()) {
            /** @var ProviderCredentialsInterface $credentials */
            $credentials = $this->serializer->deserialize($notification->getServiceCredentials(), $this->providerApi->getProviderCredentialsClass(), 'json');
        } else {
            $credentials = null;
        }

        if (!$credentials) {
            throw new \Exception('Missing parameters: provider credentials!');
        }

        $providerOrder = $this->providerApi->mapShoppingCartToProviderOrder($notification->getTenant(), $cart);
        $notification->setServicePayload($this->serializer->serialize($providerOrder, 'json'));

        $response = $this->providerApi->saveOrder($providerOrder, $credentials);

        $notification->setResponsePayload($this->serializer->serialize($response, 'json'));
        $notification->setServiceOrderId((string) $response->orderId);
        $notification->changeStatus(OrderNotification::STATUS_NOTIFIED);
        $notification->setNotifiedAt(new \DateTime());

        $this->entityManager->flush();

        $this->tenantApiService->confirmProviderNotified($notification);

        return $response;
    }

}
