<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Repository;

use Horeca\MiddlewareClientBundle\Repository\OrderNotificationRepository;

trait OrderNotificationRepositoryDI
{
    protected OrderNotificationRepository $orderNotificationRepository;

    /**
     * @required
     */
    public function setOrderNotificationRepository(OrderNotificationRepository $orderNotificationRepository): void
    {
        $this->orderNotificationRepository = $orderNotificationRepository;
    }
}
