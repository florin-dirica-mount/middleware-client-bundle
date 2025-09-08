<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\Tenant;

class TenantClientFactory implements TenantClientFactoryInterface
{
    /**
     * @var TenantClient[]
     */
    protected array $clients = [];

    public function client(Tenant $tenant): TenantClientInterface
    {
        if (!isset($this->clients[$tenant->getId()])) {
            $this->clients[$tenant->getId()] = $this->build($tenant);
        }

        return $this->clients[$tenant->getId()];
    }

    protected function build(Tenant $tenant, array $options = []): TenantClient
    {
        return new TenantClient($tenant, $options);
    }
}
