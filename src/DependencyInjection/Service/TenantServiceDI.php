<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\TenantService;
use Symfony\Contracts\Service\Attribute\Required;

trait TenantServiceDI
{
    protected TenantService $tenantService;

    #[Required]
    public function setTenantService(TenantService $tenantService): void
    {
        $this->tenantService = $tenantService;
    }
}
