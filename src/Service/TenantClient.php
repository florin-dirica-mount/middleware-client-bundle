<?php

namespace Horeca\MiddlewareClientBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\Entity\TenantWebhook;
use Horeca\MiddlewareCommonLib\Exception\HorecaException;
use Psr\Http\Message\ResponseInterface;

final class TenantClient
{
    private Client $client;

    public function __construct(private Tenant $tenant)
    {
        $this->client = new Client([
            'base_uri' => $tenant->getWebhookUrl(),
            'headers'  => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
                'Api-Key'      => $tenant->getWebhookKey()
            ],
            'timeout'  => 10,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function request(string $method, string $uri = '', array $options = []): ResponseInterface
    {
        return $this->client->request($method, $uri, $options);
    }

    public function supportsWebhook(string $name): bool
    {
        return $this->tenant->supportsWebhook($name);
    }

    public function getWebhook(string $name): ?TenantWebhook
    {
        return $this->tenant->getWebhookByName($name);
    }

    /**
     * @throws GuzzleException
     * @throws HorecaException
     */
    public function sendWebhook(TenantWebhook|string $webhook, array $options = []): ResponseInterface
    {
        if (is_string($webhook)) {
            if (!$this->supportsWebhook($webhook)) {
                throw new HorecaException(sprintf('Tenant %s does not support webhook %s', $this->tenant->getId(), $webhook));
            }

            $webhook = $this->tenant->getWebhookByName($webhook);
        }

        if (!$webhook instanceof TenantWebhook) {
            throw new HorecaException(sprintf('Invalid webhook %s', $webhook));
        }

        if (!$webhook->isEnabled()) {
            throw new HorecaException(sprintf('Tenant %s has disabled webhook %s', $this->tenant->getId(), $webhook));
        }

        return $this->client->request($webhook->getMethod(), $webhook->getPath(), $options);
    }
}