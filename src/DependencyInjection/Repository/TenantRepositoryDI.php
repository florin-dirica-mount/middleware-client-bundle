<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Repository;

use Horeca\MiddlewareClientBundle\Repository\TenantRepository;
use Symfony\Contracts\Service\Attribute\Required;

trait TenantRepositoryDI
{
    protected TenantRepository $tenantRepository;

    #[Required]
    public function setTenantRepository(TenantRepository $repository): void
    {
        $this->tenantRepository = $repository;
    }
}
