<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\TenantRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantApiServiceDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\Enum\OrderNotificationStatus;
use Horeca\MiddlewareClientBundle\Enum\ValidationGroups;
use Horeca\MiddlewareClientBundle\Exception\ApiException;
use Horeca\MiddlewareClientBundle\Exception\OrderMappingException;
use Horeca\MiddlewareClientBundle\VO\Provider\BaseProviderOrderResponse;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderOrderInterface;
use Horeca\MiddlewareCommonLib\Exception\HorecaException;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProtocolActionsService
{
    const AUTHORIZATION_HEADER = 'Authorization';
    const API_KEY_HEADER = 'Api-Key';

    use ProviderApiDI;
    use TenantRepositoryDI;
    use TenantApiServiceDI;
    use TenantServiceDI;

    public function __construct(protected string                 $providerCredentialsClass,
                                protected EntityManagerInterface $entityManager,
                                protected LoggerInterface        $logger,
                                protected ValidatorInterface     $validator,
                                protected SerializerInterface    $serializer)
    {
    }

    /**
     * @throws HorecaException
     */
    public function authorizeTenant(Request $request): Tenant
    {
        if ($auth = $request->headers->get(self::AUTHORIZATION_HEADER)) {
            $credentials = base64_decode(substr($auth, 6));
            list($id, $apiKey) = explode(':', $credentials);

            $tenant = $this->tenantRepository->findOneByApiKeyAndId($apiKey, $id);
        } elseif ($auth = $request->headers->get(self::API_KEY_HEADER)) {
            $tenant = $this->tenantRepository->findOneByApiKey((string) $auth);
        } else {
            $tenant = null;
        }

        if (!$tenant) {
            throw new HorecaException('Invalid credentials');
        }

        return $tenant;
    }

    public function sendProviderOrderToTenant(OrderNotification $notification): ?SendShoppingCartResponse
    {
        if (!$notification->getServicePayload() || !$notification->getRestaurantId()) {
            $this->logger->warning('[handleExternalServiceOrderNotification] missing ServicePayload or RestaurantId. Action aborted for notification: ' . $notification->getId());

            $notification->changeStatus(OrderNotificationStatus::Failed);
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
        $notification->changeStatus(OrderNotificationStatus::Notified);
        $notification->setNotifiedAt(new \DateTime());

        $this->entityManager->flush();

        return $response;
    }

    /**
     * @throws ApiException
     */
    public function mapTenantOrderToProviderOrder(OrderNotification $order): ProviderOrderInterface
    {
        /** @var ShoppingCart $cart */
        $cart = $this->serializer->deserialize($order->getHorecaPayload(), ShoppingCart::class, 'json');

        $errors = $this->validator->validate($cart, null, [ValidationGroups::Default, ValidationGroups::Middleware]);
        if (count($errors) > 0) {
            throw new OrderMappingException($errors->get(0)->getMessage());
        }

        $providerOrder = $this->providerApi->mapShoppingCartToProviderOrder($order->getTenant(), $cart);
        $order->setServicePayload($this->serializer->serialize($providerOrder, 'json'));

        $order->changeStatus(OrderNotificationStatus::Mapped);
        $order->setNotifiedAt(new \DateTime());

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $providerOrder;
    }

    /**
     * @throws \Exception
     */
    public function sendTenantOrderToProvider(OrderNotification $notification): ?BaseProviderOrderResponse
    {
        $providerOrder = $this->serializer->deserialize($notification->getServicePayload(), $this->providerApi->getProviderOrderClass(), 'json');

        $errors = $this->validator->validate($providerOrder, null, [ValidationGroups::Default, ValidationGroups::Middleware]);
        if (count($errors) > 0) {
            throw new OrderMappingException($errors->get(0)->getMessage());
        }

        $credentials = $this->tenantService->compileTenantCredentials($notification->getTenant(), $notification->getServiceCredentials());
        $response = $this->providerApi->saveOrder($providerOrder, $credentials);

        $notification->setResponsePayload($this->serializer->serialize($response, 'json'));
        $notification->setServiceOrderId((string) $response->orderId);
        $notification->changeStatus(OrderNotificationStatus::Notified);
        $notification->setNotifiedAt(new \DateTime());

        $this->entityManager->flush();

        return $response;
    }

    /**
     * @throws OrderMappingException
     */
    public function confirmTenantOrderProcessed(OrderNotification $notification): void
    {
        if ($notification->getStatus() !== OrderNotificationStatus::Notified) {
            $this->logger->warning('[sendTenantOrderStatusNotification] notification status is not notified. Action aborted for notification: ' . $notification->getId());

            throw new OrderMappingException('Order is not sent to provider.');
        }

        $this->tenantApiService->confirmProviderNotified($notification);

        $notification->changeStatus(OrderNotificationStatus::Confirmed);

        $this->entityManager->flush();
    }

}
