<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Doctrine\ORM\Mapping\MappingException;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\OrderNotificationRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Repository\TenantRepositoryDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\MappingLoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\ProviderApiDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantApiServiceDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantServiceDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\Enum\MappingNotificationStatus;
use Horeca\MiddlewareClientBundle\Enum\OrderNotificationType;
use Horeca\MiddlewareClientBundle\Exception\ApiException;
use Horeca\MiddlewareClientBundle\Exception\OrderMappingException;
use Horeca\MiddlewareClientBundle\VO\Provider\BaseProviderOrderResponse;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderOrderPayloadInterface;
use Horeca\MiddlewareClientBundle\VO\Provider\TestProviderOrderInterface;
use Horeca\MiddlewareCommonLib\Exception\HorecaException;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;
use Horeca\MiddlewareCommonLib\Model\Protocol\ShoppingCartStatusUpdate;
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
    use MappingLoggerDI;

    public function __construct(protected string              $providerCredentialsClass,
                                protected LoggerInterface     $logger,
                                protected ValidatorInterface  $validator,
                                protected SerializerInterface $serializer)
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
            $tenant = $this->tenantRepository->findOneByApiKey((string)$auth);
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
        $notification->setTenantPayloadString($this->serializer->serialize($cart, 'json'));

        $notification->changeStatus(MappingNotificationStatus::Mapped);
        //notified at represent time when order was mapped and send to tenant
//        $notification->setNotifiedAt(new \DateTime());

        $this->orderNotificationRepository->save($notification);

        return $providerOrder;
    }

    public function sendProviderOrderToTenant(OrderNotification $notification): ?SendShoppingCartResponse
    {
//        if (empty($notification->getServicePayload()) || !$notification->getTenantShopId()) {
        if (!$notification->getTenantPayloadString()) {
            $this->logger->warning('[handleExternalServiceOrderNotification] missing ProviderPayload. Action aborted for notification: ' . $notification->getId());

            $notification->changeStatus(MappingNotificationStatus::Failed);
            $notification->setErrorMessage('Missing ProviderPayload. Action aborted');

            $this->orderNotificationRepository->save($notification);
            return null;
        }
        $cart = $this->serializer->deserialize($notification->getTenantPayloadString(), ShoppingCart::class, 'json');
        $tenant = $notification->getTenant();
        $viewUrl = $notification->getViewUrl();

        //todo remove after all clients are updated tenant shop id shouldn't be required
        if (!$notification->getTenantShopId() && !$cart->getRestaurant()?->getId()) {
            $this->logger->warning('[handleExternalServiceOrderNotification] missing ProviderPayload or RestaurantId. Action aborted for notification: ' . $notification->getId());

            $notification->changeStatus(MappingNotificationStatus::Failed);
            $notification->setErrorMessage('Missing TenantShopId. Action aborted');

            $this->orderNotificationRepository->save($notification);
            return null;
        }

//        provider to tenant validation differs from tenant to provider validation todo move it
//        $errors = $this->validator->validate($cart);
//        if (count($errors) > 0) {
//            throw new OrderMappingException($errors->get(0)->getMessage());
//        }


        $response = $this->tenantApiService->sendShoppingCart($tenant, $cart, $notification->getTenantShopId(), $viewUrl);

        $notification->setResponsePayloadString($this->serializer->serialize($response, 'json'));
        $notification->setTenantObjectId((string)$response->horecaOrderId);
        $notification->changeStatus(MappingNotificationStatus::Notified);
        $notification->setNotifiedAt(new \DateTime());

        $this->mappingLogger->saveTo($notification, 'sendProviderOrderToTenant::');

        $this->orderNotificationRepository->save($notification);

        return $response;
    }

    public function sendProviderOrderUpdateToTenant(OrderNotification $notification): ?SendShoppingCartResponse
    {
        if (!$notification->getTenantPayloadString()) {
            $this->logger->warning('[handleExternalServiceOrderNotification] missing ProviderPayload. Action aborted for notification: ' . $notification->getId());

            $notification->changeStatus(MappingNotificationStatus::Failed);
            $notification->setErrorMessage('Missing ProviderPayload. Action aborted');

            $this->orderNotificationRepository->save($notification);
            return null;
        }

        /**
         * @var ShoppingCartStatusUpdate $updateData
         */
        $updateData = $this->serializer->deserialize($notification->getTenantPayloadString(), ShoppingCartStatusUpdate::class, 'json');

        $response = $this->tenantApiService->sendShoppingCartUpdate(
            tenant: $notification->getTenant(),
            json: $notification->getTenantPayloadString(),
            viewUrl: $notification->getViewUrl(),
            eventType: $updateData->eventType
        );

        $notification->setResponsePayloadString($this->serializer->serialize($response, 'json'));
        $notification->setTenantObjectId((string)$response->horecaOrderId);
        $notification->changeStatus(MappingNotificationStatus::Notified);
        $notification->setNotifiedAt(new \DateTime());

        $this->orderNotificationRepository->save($notification);

        return $response;
    }

    /**
     * @throws ApiException
     */
//    public function mapTenantOrderToProviderOrder(OrderNotification $notification): ProviderOrderPayloadInterface
    public function mapTenantOrderToProviderOrder(OrderNotification $notification): void
    {
        /** @var ShoppingCart $cart */
        $cart = $this->serializer->deserialize($notification->getTenantPayloadString(), ShoppingCart::class, 'json');

        $notification->setErrorMessage(null);
        if ($notification->isType(OrderNotificationType::NewOrder)) {

            $errors = $this->validator->validate($cart);
            if (count($errors) > 0) {
                throw new OrderMappingException($errors->get(0)->getMessage());
            }

            $providerOrder = $this->providerApi->mapShoppingCartToProviderOrder($notification->getTenant(), $cart);

        } else {
            if ($this->providerApi instanceof OrderUpdatesApiInterface) {
                $providerOrder = $this->providerApi->mapTenantOrderUpdateToProvider($notification);
            } else {
                throw new OrderMappingException('Provider does not support order updates, implement OrderUpdatesApiInterface');
            }
        }
        $notification->setProviderPayloadString($this->serializer->serialize($providerOrder, 'json'));

        $notification->changeStatus(MappingNotificationStatus::Mapped);

        $this->orderNotificationRepository->save($notification);

//        if ($cart->isTestOrder()) {
//            throw  new MappingException('Order is marked as  test order');
//        }

//        return $providerOrder;
    }

    /**
     * @throws \Exception
     */
    public function sendTenantOrderToProvider(OrderNotification $notification): BaseProviderOrderResponse
    {


        $credentials = $this->tenantService->compileTenantCredentials($notification->getTenant(), $notification->getServiceCredentials());

        if ($notification->isType(OrderNotificationType::NewOrder)) {

            $providerOrder = $this->serializer->deserialize($notification->getProviderPayloadString(), $this->providerApi->getMiddlewareToProviderOrderClass(), 'json');

            if ($providerOrder instanceof TestProviderOrderInterface && $providerOrder->isTestOrder()) {
                throw new MappingException('Order is marked as test order');
            }

            $errors = $this->validator->validate($providerOrder);
            if (count($errors) > 0) {
                throw new OrderMappingException($errors->get(0)->getMessage());
            }

            $response = $this->providerApi->sendOrderToProvider($providerOrder, $credentials, $notification->getServiceCredentials());
        } else {
            if ($this->providerApi instanceof OrderUpdatesApiInterface) {
                $response = $this->providerApi->sendTenantOrderUpdateToProvider($notification);
            } else {
                throw new OrderMappingException('Provider does not support order updates, implement OrderUpdatesApiInterface');
            }
        }

        $notification->setResponsePayloadString($this->serializer->serialize($response, 'json'));
        $notification->setProviderObjectId((string)$response->orderId);
        $notification->changeStatus(MappingNotificationStatus::Notified);
        $notification->setNotifiedAt(new \DateTime());

        $this->orderNotificationRepository->save($notification);

        return $response;
    }


}
