<?php

namespace Horeca\MiddlewareClientBundle\Service;

use GuzzleHttp\Client;
use Horeca\MiddlewareClientBundle\Entity\Tenant;

final class TenantClientFactory
{
    /**
     * @var Client[]
     */
    protected array $clients = [];

    public function client(Tenant $tenant): Client
    {
        if (!isset($this->clients[$tenant->getId()])) {
            $this->clients[$tenant->getId()] = $this->build($tenant);
        }

        return $this->clients[$tenant->getId()];
    }

    private function build(Tenant $tenant): Client
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