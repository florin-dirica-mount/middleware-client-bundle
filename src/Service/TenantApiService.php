<?php

namespace Horeca\MiddlewareClientBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\SerializerDI;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareCommonLib\Exception\HorecaException;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;
use Symfony\Component\HttpFoundation\Response;

class TenantApiService implements TenantApiServiceInterface
{
    use SerializerDI;

    private ?Client $client = null;

    /**
     * @throws HorecaException
     */
    public function confirmProviderNotified(OrderNotification $notification): bool
    {
        try {
            $cart = $this->deserializeJson($notification->getHorecaPayload(), ShoppingCart::class);

            if (!$cart->getId()) {
                throw new HorecaException('Missing API parameter: cart.id');
            }

            $uri = sprintf('/middleware/cart/%s/confirm-provider-notified', $cart->getId());
            $response = $this->getClient($notification->getTenant())->post($uri);

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
            $uri = sprintf('/middleware/order/%s', $restaurantId);

            $response = $this->getClient($tenant)->post($uri, [
                'json' => json_decode($this->serializer->serialize($cart, 'json'), true)
            ]);

            return $this->serializer->deserialize($response->getBody()->getContents(), SendShoppingCartResponse::class, 'json');
        } catch (GuzzleException|\Exception $e) {
            throw new HorecaException($e->getMessage());
        }
    }

    private function getClient(Tenant $tenant): Client
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => $tenant->getWebhookUrl(),
                'headers'  => [
                    'Api-Key' => $tenant->getWebhookKey(),
                ],
                'timeout'  => 15
            ]);
        }

        return $this->client;
    }

}
