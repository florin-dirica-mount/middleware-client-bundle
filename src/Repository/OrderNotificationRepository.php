<?php

namespace Horeca\MiddlewareClientBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Enum\OrderNotificationStatus;

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

    public function findOneByHorecaOrderId(string $id): ?OrderNotification
    {
        return $this->findOneBy(['horecaOrderId' => $id]);
    }
}
