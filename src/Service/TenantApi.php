<?php

namespace Horeca\MiddlewareClientBundle\Service;

use GuzzleHttp\Exception\GuzzleException;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantClientFactoryDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\Enum\SerializationGroups;
use Horeca\MiddlewareClientBundle\VO\Api\OrderNotificationEventDto;
use Horeca\MiddlewareCommonLib\Exception\HorecaException;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class TenantApi implements TenantApiInterface
{
    protected const NOTIFICATION_EVENT_PATH = '/middleware/notification/event';
    protected const SEND_SHOPPING_CART_PATH = '/middleware/order/%s';

    use TenantClientFactoryDI;

    public function __construct(protected SerializerInterface $serializer) { }

    /**
     * @throws HorecaException
     */
    public function sendOrderNotificationEvent(string $event, OrderNotification $notification): void
    {
        try {
            $data = new OrderNotificationEventDto($event, $notification);
            $context = SerializationContext::create()->setGroups(SerializationGroups::TenantOrderNotificationView);
            $options['body'] = $this->serializer->serialize($data, 'json', $context);

            $response = $this->tenantClientFactory->client($notification->getTenant())->post(self::NOTIFICATION_EVENT_PATH, $options);

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw new HorecaException('Tenant API error. Status code: ' . $response->getStatusCode());
            }
        } catch (GuzzleException|\Exception $e) {
            throw new HorecaException($e->getMessage());
        }
    }

    /**
     * @throws HorecaException
     */
    public function confirmProviderNotified(OrderNotification $notification): bool
    {
        try {
            $cart = $this->serializer->deserialize($notification->getHorecaPayloadString(), ShoppingCart::class, 'json');

            if (!$cart->getId()) {
                throw new HorecaException('Missing API parameter: cart.id');
            }

            $uri = sprintf('/middleware/cart/%s/confirm-provider-notified', $cart->getId());
            $response = $this->tenantClientFactory->client($notification->getTenant())->post($uri);

            return $response->getStatusCode() === Response::HTTP_OK;
        } catch (GuzzleException|\Exception $e) {
            throw new HorecaException($e->getMessage());
        }
    }

    /**
     * @throws HorecaException
     */
    public function sendShoppingCart(Tenant $tenant, ShoppingCart $cart, $restaurantId): SendShoppingCartResponse
    {
        try {
            $uri = sprintf(self::SEND_SHOPPING_CART_PATH, $restaurantId);
            $options['json'] = json_decode($this->serializer->serialize($cart, 'json'), true);

            $response = $this->tenantClientFactory->client($tenant)->post($uri, $options);

            return $this->serializer->deserialize($response->getBody()->getContents(), SendShoppingCartResponse::class, 'json');
        } catch (GuzzleException|\Exception $e) {
            throw new HorecaException($e->getMessage());
        }
    }
}
