<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\TenantClientFactoryInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait TenantClientFactoryDI
{
    protected TenantClientFactoryInterface $tenantClientFactory;

    #[Required]
    public function setTenantClientFactory(TenantClientFactoryInterface $tenantClientFactory): void
    {
        $this->tenantClientFactory = $tenantClientFactory;
    }
}