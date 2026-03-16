<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\ProductNotification;

interface ProductMapperApiInterface
{
    public function mapTenantProductToProvider(ProductNotification $notification): ProductNotification;

    public function sendTenantProductToProvider(ProductNotification $notification): ProductNotification;

    public function mapProviderProductToTenant(ProductNotification $notification): ProductNotification;

    public function sendProviderProductToTenant(ProductNotification $notification): ProductNotification;


}
