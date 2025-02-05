<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\Tenant;
use Horeca\MiddlewareCommonLib\Model\Restaurant\Restaurant;

interface UpdateShopApiInterface
{
    public function updateShopAvailability(Tenant $tenant, string $tenantShopId, Restaurant $restaurant): bool;


}
