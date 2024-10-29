<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\Tenant;

interface InitializeShopApiInterface
{
    public function initializeShop(Tenant $tenant, string $tenantShopId, string $providerShopId, ?string $shopName): bool;


}
