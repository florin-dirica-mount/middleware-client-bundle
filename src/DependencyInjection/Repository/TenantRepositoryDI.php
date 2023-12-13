<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Repository;

use Horeca\MiddlewareClientBundle\Repository\TenantRepository;

trait TenantRepositoryDI
{
    protected TenantRepository $tenantRepository;

    /**
     * @required
     */
    public function setTenantRepository(TenantRepository $repository): void
    {
        $this->tenantRepository = $repository;
    }
}