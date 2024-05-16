<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Repository;

use Horeca\MiddlewareClientBundle\Repository\MenuNotificationRepository;
use Symfony\Contracts\Service\Attribute\Required;

trait MenuNotificationRepositoryDI
{
    protected MenuNotificationRepository $menuNotificationRepository;

    #[Required]
    public function setMenuNotificationRepository(MenuNotificationRepository $menuNotificationRepository): void
    {
        $this->menuNotificationRepository = $menuNotificationRepository;
    }
}
