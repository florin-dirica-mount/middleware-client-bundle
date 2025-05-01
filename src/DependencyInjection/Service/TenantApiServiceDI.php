<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\TenantApiInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait TenantApiServiceDI
{
    protected TenantApiInterface $tenantApiService;

    #[Required]
    public function setTenantApiService(TenantApiInterface $tenantApiService): void
    {
        $this->tenantApiService = $tenantApiService;
    }
}
