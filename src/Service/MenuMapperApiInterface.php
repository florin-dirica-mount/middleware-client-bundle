<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\MenuNotification;

interface MenuMapperApiInterface
{
    public function mapTenantMenuToProvider(MenuNotification $notification): bool;
    public function sendTenantMenuToProvider(MenuNotification $notification): bool;


}
