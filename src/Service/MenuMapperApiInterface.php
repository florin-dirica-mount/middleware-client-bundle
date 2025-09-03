<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\MappingNotification;
use Horeca\MiddlewareClientBundle\Entity\MenuNotification;

interface MenuMapperApiInterface
{
    public function mapTenantMenuToProvider(MenuNotification $notification): MappingNotification;
    public function sendTenantMenuToProvider(MenuNotification $notification): MappingNotification;

    public function mapProviderMenuToTenant(MenuNotification $notification): MappingNotification;
    public function sendProviderMenuToTenant(MenuNotification $notification): MappingNotification;



}
