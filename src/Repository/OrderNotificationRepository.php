<?php

namespace Horeca\MiddlewareClientBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\Tenant;

/**
 * @method OrderNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderNotification[]|array findAll()
 * @method OrderNotification[]|array findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderNotificationRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderNotification::class);
    }

    /**
     * @deprecated use self::findOneByTenantOrderId
     */
    public function findOneByHorecaOrderId(string $id): ?OrderNotification
    {
        return $this->findOneBy(['horecaOrderId' => $id]);
    }

    public function findOneByTenantOrderId(Tenant $tenant, string $id): ?OrderNotification
    {
        return $this->findOneBy(['tenant' => $tenant, 'tenantObjectId' => $id]);
    }

    public function findOneByTenantAndProviderOrderId(Tenant $tenant, string $id): ?OrderNotification
    {
        return $this->findOneBy(['tenant' => $tenant, 'providerObjectId' => $id]);
    }

    public function findOneByTenantTypeAndProviderOrderId(Tenant $tenant,string $type, string $id): ?OrderNotification
    {
        return $this->findOneBy(['tenant' => $tenant, 'providerObjectId' => $id, 'type' => $type]);
    }

    public function findOneByProviderOrderId(string $id): ?OrderNotification
    {
        return $this->findOneBy(['providerObjectId' => $id]);
    }

    public function save(OrderNotification $notification): void
    {
        $this->_em->persist($notification);
        $this->_em->flush();
    }
}
