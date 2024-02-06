<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\TenantClientFactory;
use Symfony\Contracts\Service\Attribute\Required;

trait TenantClientFactoryDI
{
    protected TenantClientFactory $tenantClientFactory;

    #[Required]
    public function setTenantClientFactory(TenantClientFactory $tenantClientFactory): void
    {
        $this->tenantClientFactory = $tenantClientFactory;
    }
}