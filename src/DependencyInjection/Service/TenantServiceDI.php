<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\TenantService;

trait TenantServiceDI
{
    protected TenantService $tenantService;

    /**
     * @required
     */
    public function setTenantService(TenantService $tenantService): void
    {
        $this->tenantService = $tenantService;
    }
}