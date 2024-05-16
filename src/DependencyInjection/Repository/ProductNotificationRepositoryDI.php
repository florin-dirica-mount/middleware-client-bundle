<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Repository;

use Horeca\MiddlewareClientBundle\Repository\MenuNotificationRepository;
use Horeca\MiddlewareClientBundle\Repository\ProductNotificationRepository;
use Symfony\Contracts\Service\Attribute\Required;

trait ProductNotificationRepositoryDI
{
    protected ProductNotificationRepository $productNotificationRepository;

    #[Required]
    public function setProductNotificationRepository(ProductNotificationRepository $productNotificationRepository): void
    {
        $this->productNotificationRepository = $productNotificationRepository;
    }
}
