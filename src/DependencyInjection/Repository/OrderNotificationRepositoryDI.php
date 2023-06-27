<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Repository\OrderNotificationRepository;

trait OrderNotificationRepositoryDI
{
    protected OrderNotificationRepository $orderNotificationRepository;

    /**
     * @required
     */
    public function setOrderNotificationRepository(EntityManagerInterface $entityManager): void
    {
        $this->orderNotificationRepository = $entityManager->getRepository(OrderNotification::class);
    }
}
