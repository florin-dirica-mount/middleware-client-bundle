<?php

namespace Horeca\MiddlewareClientBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Horeca\MiddlewareClientBundle\Entity\Tenant;

final class TenantClientFactory
{
    /**
     * @var ClientInterface[]
     */
    protected array $clients = [];

    public function client(Tenant $tenant): ClientInterface
    {
        if (!isset($this->clients[$tenant->getId()])) {
            $this->clients[$tenant->getId()] = $this->build($tenant);
        }

        return $this->clients[$tenant->getId()];
    }

    private function build(Tenant $tenant): ClientInterface
    {
        return new Client([
            'base_uri' => $tenant->getWebhookUrl(),
            'headers'  => [
                'Accept'  => 'application/json',
                'Api-Key' => $tenant->getWebhookKey()
            ],
            'timeout'  => 10,
        ]);
    }
}