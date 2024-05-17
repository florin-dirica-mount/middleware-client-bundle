<?php

namespace Horeca\MiddlewareClientBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Horeca\MiddlewareClientBundle\Entity\ProductNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;

/**
 * @method ProductNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductNotification[]|array findAll()
 * @method ProductNotification[]|array findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductNotificationRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductNotification::class);
    }

    /**
     * @deprecated use self::findOneByTenantOrderId
     */
    public function findOneByHorecaOrderId(string $id): ?ProductNotification
    {
        return $this->findOneBy(['horecaOrderId' => $id]);
    }

    public function findOneByTenantOrderId(Tenant $tenant, string $id): ?ProductNotification
    {
        return $this->findOneBy(['tenant' => $tenant, 'tenantObjectId' => $id]);
    }

    public function findOneByTenantAndProviderOrderId(Tenant $tenant, string $id): ?ProductNotification
    {
        return $this->findOneBy(['tenant' => $tenant, 'providerObjectId' => $id]);
    }

    public function findOneByProviderOrderId(string $id): ?ProductNotification
    {
        return $this->findOneBy(['providerObjectId' => $id]);
    }

    public function save(ProductNotification $notification): void
    {
        $this->_em->persist($notification);
        $this->_em->flush();
    }
}
