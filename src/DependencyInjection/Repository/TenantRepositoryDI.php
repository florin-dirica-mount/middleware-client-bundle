<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareClientBundle\Repository\TenantRepository;

trait TenantRepositoryDI
{
    protected TenantRepository $tenantRepository;

    /**
     * @required
     */
    public function setTenantRepository(EntityManagerInterface $entityManager): void
    {
        $this->tenantRepository = $entityManager->getRepository(Tenant::class);
    }
}