<?php

namespace Horeca\MiddlewareClientBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareCommonLib\Exception\HorecaException;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use Horeca\MiddlewareCommonLib\Model\Protocol\SendShoppingCartResponse;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class TenantApi implements TenantApiInterface
{
    /**
     * @var Client[]
     */
    private array $client = [];

    protected SerializerInterface $serializer;

    /**
     * @required
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function getShoppingCartProductMappings(ShoppingCart $cart): array
    {
        // TODO: Implement getShoppingCartProductMappings() method.
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
        if (!isset($this->client[$tenant->getId()])) {

            $this->client[$tenant->getId()] = new Client([
                'base_uri' => $tenant->getWebhookUrl(),
                'headers'  => [
                    'Api-Key' => $tenant->getWebhookKey(),
                ],
                'timeout'  => 15
            ]);
        }

        return $this->client[$tenant->getId()];
    }

}
