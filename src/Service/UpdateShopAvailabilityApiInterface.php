<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\Tenant;

interface UpdateShopAvailabilityApiInterface
{
    public function updateShopAvailability(Tenant $tenant, string $tenantShopId, bool $open): bool;


}
