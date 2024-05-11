<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\OrderNotificationRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\TenantRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantApiServiceDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\Enum\OrderNotificationStatus;
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

    use OrderNotificationRepositoryDI;
    use ProviderApiDI;
    use TenantRepositoryDI;
    use TenantApiServiceDI;
    use TenantServiceDI;

    public function __construct(protected string                 $providerCredentialsClass,
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

    public function mapProviderOrderToTenantOrder(OrderNotification $notification)
    {
        $providerOrder = $this->serializer->deserialize($notification->getProviderPayloadString(), $this->providerApi->getProviderToMiddlewareOrderClass(), 'json');

        $errors = $this->validator->validate($providerOrder);
        if (count($errors) > 0) {
            throw new OrderMappingException($errors->get(0)->getMessage());
        }

//        $notification->setErrorMessage(null);
        $cart = $this->providerApi->mapProviderOrderToShoppingCart($notification->getTenant(), $providerOrder);
        $notification->setHorecaPayloadString($this->serializer->serialize($cart, 'json'));

        $notification->changeStatus(OrderNotificationStatus::Mapped);
        //notified at represent time when order was mapped and send to tenant
//        $notification->setNotifiedAt(new \DateTime());

        $this->orderNotificationRepository->save($notification);

        return $providerOrder;
    }

    public function sendProviderOrderToTenant(OrderNotification $notification): ?SendShoppingCartResponse
    {
//        if (empty($notification->getServicePayload()) || !$notification->getTenantShopId()) {
        if (!$notification->getTenantShopId()) {
            $this->logger->warning('[handleExternalServiceOrderNotification] missing ServicePayload or RestaurantId. Action aborted for notification: ' . $notification->getId());

            $notification->changeStatus(OrderNotificationStatus::Failed);
            $notification->setErrorMessage('Missing ServicePayload. Action aborted');

            $this->orderNotificationRepository->save($notification);

            return null;
        }

        $cart = $this->serializer->deserialize($notification->getHorecaPayloadString(), ShoppingCart::class, 'json');
// provider to tenant validation differs from tenant to provider validation
//        $errors = $this->validator->validate($cart);
//        if (count($errors) > 0) {
//            throw new OrderMappingException($errors->get(0)->getMessage());
//        }

        $response = $this->tenantApiService->sendShoppingCart($notification->getTenant(), $cart, $notification->getTenantShopId());

        $notification->setResponsePayloadString($this->serializer->serialize($response, 'json'));
        $notification->setTenantObjectId((string) $response->horecaOrderId);
        $notification->changeStatus(OrderNotificationStatus::Notified);
        $notification->setNotifiedAt(new \DateTime());

        $this->orderNotificationRepository->save($notification);

        return $response;
    }

    /**
     * @throws ApiException
     */
    public function mapTenantOrderToProviderOrder(OrderNotification $notification): ProviderOrderInterface
    {
        /** @var ShoppingCart $cart */
        $cart = $this->serializer->deserialize($notification->getHorecaPayloadString(), ShoppingCart::class, 'json');

        $errors = $this->validator->validate($cart);
        if (count($errors) > 0) {
            throw new OrderMappingException($errors->get(0)->getMessage());
        }

        $notification->setErrorMessage(null);
        $providerOrder = $this->providerApi->mapShoppingCartToProviderOrder($notification->getTenant(), $cart);
        $notification->setProviderPayloadString($this->serializer->serialize($providerOrder, 'json'));

        $notification->changeStatus(OrderNotificationStatus::Mapped);
//        $notification->setNotifiedAt(new \DateTime());

        $this->orderNotificationRepository->save($notification);

        return $providerOrder;
    }

    /**
     * @throws \Exception
     */
    public function sendTenantOrderToProvider(OrderNotification $notification): BaseProviderOrderResponse
    {
        $providerOrder = $this->serializer->deserialize($notification->getProviderPayloadString(), $this->providerApi->getMiddlewareToProviderOrderClass(), 'json');

        $errors = $this->validator->validate($providerOrder);
        if (count($errors) > 0) {
            throw new OrderMappingException($errors->get(0)->getMessage());
        }

        $credentials = $this->tenantService->compileTenantCredentials($notification->getTenant(), $notification->getServiceCredentials());
        $response = $this->providerApi->sendOrderToProvider($providerOrder, $credentials);

        $notification->setResponsePayloadString($this->serializer->serialize($response, 'json'));
        $notification->setProviderObjectId((string) $response->orderId);
        $notification->changeStatus(OrderNotificationStatus::Notified);
        $notification->setNotifiedAt(new \DateTime());

        $this->orderNotificationRepository->save($notification);

        return $response;
    }

    /**
     * @throws OrderMappingException
     * @deprecated
     */
    public function confirmTenantOrderProcessed(OrderNotification $notification): void
    {
        if ($notification->getStatus() !== OrderNotificationStatus::Notified) {
            $this->logger->warning('[sendTenantOrderStatusNotification] notification status is not notified. Action aborted for notification: ' . $notification->getId());

            throw new OrderMappingException('Order is not sent to provider.');
        }

        $this->tenantApiService->confirmProviderNotified($notification);

        $notification->changeStatus(OrderNotificationStatus::Confirmed);

        $this->orderNotificationRepository->save($notification);
    }

}
