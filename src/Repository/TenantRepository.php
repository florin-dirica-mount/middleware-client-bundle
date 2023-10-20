<?php

namespace Horeca\MiddlewareClientBundle\Repository;

use Horeca\MiddlewareClientBundle\Entity\BaseTenantCredentials;
use Horeca\MiddlewareClientBundle\Entity\Tenant;

/**
 * @method Tenant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tenant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tenant[]|array findAll()
 * @method Tenant[]|array findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TenantRepository extends ExtendedEntityRepository
{

    public function findOneByApiKey(string $key): ?Tenant
    {
        return $this->findOneBy(['apiKey' => $key]);
    }

    /**
     * @throws \Exception
     */
    public function findTenantCredentials(Tenant $tenant, string $credentialsClass): ?BaseTenantCredentials
    {
        if (!class_exists($credentialsClass)) {
            throw new \Exception(sprintf('Class %s does not exist', $credentialsClass));
        }
        if (!is_subclass_of($credentialsClass, BaseTenantCredentials::class)) {
            throw new \Exception(sprintf('Class %s is not a subclass of %s', $credentialsClass, BaseTenantCredentials::class));
        }

        $repo = $this->getEntityManager()->getRepository($credentialsClass);

        return $repo->findOneBy(['tenant' => $tenant]);
    }
}