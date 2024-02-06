<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\TenantApiInterface;

trait TenantApiServiceDI
{
    protected TenantApiInterface $tenantApiService;

    /**
     * @required
     * @param TenantApiInterface $tenantApiService
     */
    public function setTenantApiService(TenantApiInterface $tenantApiService): void
    {
        $this->tenantApiService = $tenantApiService;
    }
}
