<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;


use Horeca\MiddlewareClientBundle\Service\TenantApiService;

trait TenantApiServiceDI
{
    /**
     * @var TenantApiService
     */
    protected $tenantApiService;

    /**
     * @required
     * @param TenantApiService $tenantApiService
     */
    public function setTenantApiService(TenantApiService $tenantApiService)
    {
        $this->tenantApiService = $tenantApiService;
    }
}
