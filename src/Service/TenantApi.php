<?php

namespace Horeca\MiddlewareClientBundle\Service;

use GuzzleHttp\Exception\GuzzleException;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\OrderLoggerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Service\TenantClientFactoryDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\Enum\SerializationGroups;
use Horeca\MiddlewareClientBundle\Enum\TenantWebhookName;
use Horeca\MiddlewareClientBundle\VO\Api\OrderNotificationEventDto;
use Horeca\MiddlewareCommonLib\Exception\HorecaException;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class TenantApi implements TenantApiInterface
{
    use TenantClientFactoryDI;
    use OrderLoggerDI;

    public function __construct(protected SerializerInterface $serializer) { }

    /**
     * @throws HorecaException
     */
    public function sendOrderNotificationEvent(string $event, OrderNotification $notification): void
    {
        $client = $this->tenantClientFactory->client($notification->getTenant());
        $webhook = $client->getWebhook(TenantWebhookName::WEBHOOK_ORDER_NOTIFICATION_EVENT);

        $data = new OrderNotificationEventDto($event, $notification);
        $context = SerializationContext::create()->setGroups(SerializationGroups::TenantOrderNotificationView);
        $json = $this->serializer->serialize($data, 'json', $context);

        try {
            if ($webhook->getMethod() === 'GET') {
                $options['query']['payload'] = base64_encode($json);
            } else {
                $options['body'] = $json;
            }

            $this->orderLogger->info(__METHOD__, __LINE__, sprintf('%s %s ', $webhook->getMethod(), $webhook->getPath()));

            $response = $client->sendWebhook($webhook, $options);
            $contents = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();

            $this->orderLogger->info(__METHOD__, __LINE__, sprintf('Response: %d %s', $statusCode, $contents));

            if ($statusCode !== Response::HTTP_OK) {
                throw new HorecaException(sprintf('[TenantApi.sendOrderNotificationEvent] Error %d: %s. Request payload: %s', $statusCode, $contents, $json));
            }
        } catch (GuzzleException|\Exception $e) {
            throw new HorecaException(sprintf('[TenantApi.sendOrderNotificationEvent] Error: %s. Request payload: %s', $e->getMessage(), $json));
        }
    }

    /**
     * @throws HorecaException
     * @deprecated
     */
    public function confirmProviderNotified(OrderNotification $notification): bool
    {
        try {
            $cart = $this->serializer->deserialize($notification->getHorecaPayloadString(), ShoppingCart::class, 'json');

            if (!$cart->getId()) {
                throw new HorecaException('Missing API parameter: cart.id');
            }

            $uri = sprintf('/middleware/cart/%s/confirm-provider-notified', $cart->getId());

            $this->orderLogger->info(__METHOD__, __LINE__, "POST $uri");

            $response = $this->tenantClientFactory->client($notification->getTenant())->request('POST', $uri);
            $contents = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();

            $this->orderLogger->info(__METHOD__, __LINE__, sprintf('Response: %d %s', $statusCode, $contents));

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
            $client = $this->tenantClientFactory->client($tenant);
            $webhook = $client->getWebhook(TenantWebhookName::WEBHOOK_SHOPPING_CART_SEND);
            $json = $this->serializer->serialize($cart, 'json');

            if ($webhook->getMethod() === 'GET') {
                $options['query']['payload'] = base64_encode($json);
            } else {
                $options['json'] = json_decode($json, true);
            }

            $this->orderLogger->info(__METHOD__, __LINE__, sprintf('%s %s %s', $webhook->getMethod(), $webhook->getPath(), $json));

            $response = $client->sendWebhook($webhook, $options);
            $contents = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();

            $this->orderLogger->info(__METHOD__, __LINE__, sprintf('Response: %d %s', $statusCode, $contents));

            return $this->serializer->deserialize($contents, SendShoppingCartResponse::class, 'json');
        } catch (GuzzleException|\Exception $e) {
            throw new HorecaException($e->getMessage());
        }
    }
}
