<?php

namespace Horeca\MiddlewareClientBundle\Event\Menu;

use Horeca\MiddlewareClientBundle\Entity\MenuNotification;
use Symfony\Contracts\EventDispatcher\Event;

class TenantMenuEvent extends Event
{
    public const MENU_RECEIVED = 'hmc.tenant.menu.received';
    public const MENU_MAPPED = 'hmc.tenant.menu.mapped';

    public function __construct(private MenuNotification $menuNotification)
    {
    }

    public function getMenuNotification(): MenuNotification
    {
        return $this->menuNotification;
    }
}
