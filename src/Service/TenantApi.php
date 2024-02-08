<?php

namespace Horeca\MiddlewareClientBundle\Service;

use GuzzleHttp\Exception\GuzzleException;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantClientFactoryDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\Entity\TenantWebhook;
use Horeca\MiddlewareClientBundle\VO\Api\OrderNotificationEventDto;
use Horeca\MiddlewareCommonLib\Exception\HorecaException;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class TenantApi implements TenantApiInterface
{
    use TenantClientFactoryDI;

    public function __construct(protected SerializerInterface $serializer) { }

    /**
     * @throws HorecaException
     */
    public function sendOrderNotificationEvent(string $event, OrderNotification $notification): void
    {
        $webhook = $notification->getTenant()->getWebhookByName(self::WEBHOOK_ORDER_NOTIFICATION_EVENT);
        if (!$webhook || !$webhook->isEnabled()) {
            throw new HorecaException(sprintf('Tenant %s does not support webhook %s', $notification->getTenant()->getId(), self::WEBHOOK_ORDER_NOTIFICATION_EVENT));
        }

        try {
            $data = new OrderNotificationEventDto($event, $notification);

            if ($webhook->getMethod() === 'GET') {
                $options['query']['payload'] = base64_encode($this->serializer->serialize($data, 'json'));
            } else {
                $options['body'] = $this->serializer->serialize($data, 'json');
            }

            $response = $this->sendWebhook($webhook, $options);

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
        $webhook = $tenant->getWebhookByName(self::WEBHOOK_SHOPPING_CART_SEND);
        if (!$webhook || !$webhook->isEnabled()) {
            throw new HorecaException(sprintf('Tenant %s does not support webhook %s', $tenant->getId(), self::WEBHOOK_SHOPPING_CART_SEND));
        }

        try {
            if ($webhook->getMethod() === 'GET') {
                $options['query']['payload'] = base64_encode($this->serializer->serialize($cart, 'json'));
            } else {
                $options['json'] = json_decode($this->serializer->serialize($cart, 'json'), true);
            }

            $response = $this->sendWebhook($webhook, $options);

            return $this->serializer->deserialize($response->getBody()->getContents(), SendShoppingCartResponse::class, 'json');
        } catch (GuzzleException|\Exception $e) {
            throw new HorecaException($e->getMessage());
        }
    }

    /**
     * @throws GuzzleException
     */
    protected function sendWebhook(TenantWebhook $webhook, array $options = []): ResponseInterface
    {
        return $this->tenantClientFactory->client($webhook->getTenant())
            ->request($webhook->getMethod(), $webhook->getPath(), $options);
    }
}
