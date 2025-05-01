<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\Tenant;

interface SyncAndExportShopProductsApiInterface
{
    public function syncAndExportProducts(Tenant $tenant, string $tenantShopId): bool;


}
