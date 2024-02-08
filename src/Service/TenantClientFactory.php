<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\Tenant;

final class TenantClientFactory
{
    /**
     * @var TenantClient[]
     */
    protected array $clients = [];

    public function client(Tenant $tenant): TenantClient
    {
        if (!isset($this->clients[$tenant->getId()])) {
            $this->clients[$tenant->getId()] = $this->build($tenant);
        }

        return $this->clients[$tenant->getId()];
    }

    private function build(Tenant $tenant): TenantClient
    {
        return new TenantClient($tenant);
    }
}