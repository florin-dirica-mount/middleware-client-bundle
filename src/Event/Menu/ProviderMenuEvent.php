<?php

namespace Horeca\MiddlewareClientBundle\Event\Menu;

use Horeca\MiddlewareClientBundle\Entity\MenuNotification;
use Symfony\Contracts\EventDispatcher\Event;

class ProviderMenuEvent extends Event
{
    public const MENU_RECEIVED = 'hmc.provider.menu.received';
    public const MENU_MAPPED = 'hmc.provider.menu.mapped';

    public function __construct(private MenuNotification $menuNotification)
    {
    }

    public function getMenuNotification(): MenuNotification
    {
        return $this->menuNotification;
    }
}
