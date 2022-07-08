<?php

namespace Horeca\MiddlewareClientBundle\Repository;

use Horeca\MiddlewareClientBundle\Entity\OrderNotification;

/**
 * @method OrderNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderNotification[]|array findAll()
 * @method OrderNotification[]|array findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderNotificationRepository extends ExtendedEntityRepository
{
    public function findOneByHorecaOrderId(string $id): ?OrderNotification
    {
        return $this->findOneBy(['horecaOrderId' => $id]);
    }

    /**
     * @return array|OrderNotification[]
     */
    public function findPending(): array
    {
        return $this->findBy(
            ['status' => OrderNotification::STATUS_PENDING],
            ['createdAt' => 'ASC']
        );
    }
}
