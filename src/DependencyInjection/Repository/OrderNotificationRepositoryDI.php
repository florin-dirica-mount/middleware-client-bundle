<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Repository;

use Horeca\MiddlewareClientBundle\Repository\OrderNotificationRepository;
use Symfony\Contracts\Service\Attribute\Required;

trait OrderNotificationRepositoryDI
{
    protected OrderNotificationRepository $orderNotificationRepository;

    #[Required]
    public function setOrderNotificationRepository(OrderNotificationRepository $repository): void
    {
        $this->orderNotificationRepository = $repository;
    }
}
