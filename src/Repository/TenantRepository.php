<?php

namespace Horeca\MiddlewareClientBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Horeca\MiddlewareClientBundle\Entity\BaseProviderCredentials;
use Horeca\MiddlewareClientBundle\Entity\Tenant;

/**
 * @method Tenant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tenant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tenant[]|array findAll()
 * @method Tenant[]|array findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TenantRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tenant::class);
    }

    public function findOneByApiKey(string $key): ?Tenant
    {
        return $this->findOneBy([
            'apiKey' => $key,
            'active' => true
        ]);
    }

    public function findOneByApiKeyAndId(string $key, string $id): ?Tenant
    {
        return $this->findOneBy([
            'id' => $id,
            'apiKey' => $key,
            'active' => true
        ]);
    }

    /**
     * @throws \Exception
     */
    public function findTenantCredentials(Tenant $tenant, string $credentialsClass): ?BaseProviderCredentials
    {
        if (!class_exists($credentialsClass)) {
            throw new \Exception(sprintf('Class %s does not exist', $credentialsClass));
        }
        if (!is_subclass_of($credentialsClass, BaseProviderCredentials::class)) {
            throw new \Exception(sprintf('Class %s is not a subclass of %s', $credentialsClass, BaseProviderCredentials::class));
        }

        $repo = $this->_em->getRepository($credentialsClass);

        return $repo->findOneBy([
            'tenant' => $tenant,
            'active' => true
        ]);
    }
}
