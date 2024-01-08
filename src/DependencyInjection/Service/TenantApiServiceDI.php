<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\TenantApiServiceInterface;

trait TenantApiServiceDI
{
    protected TenantApiServiceInterface $tenantApiService;

    /**
     * @required
     * @param TenantApiServiceInterface $tenantApiService
     */
    public function setTenantApiService(TenantApiServiceInterface $tenantApiService): void
    {
        $this->tenantApiService = $tenantApiService;
    }
}
