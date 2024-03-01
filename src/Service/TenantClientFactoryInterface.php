<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\Entity\Tenant;

interface TenantClientFactoryInterface
{

    public function client(Tenant $tenant): TenantClientInterface;

}