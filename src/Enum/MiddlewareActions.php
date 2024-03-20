<?php

namespace Horeca\MiddlewareClientBundle\Enum;

class MiddlewareActions
{
    public const SyncTenantProducts = 'middleware.sync.tenant.products';
    public const SyncProviderProducts = 'middleware.sync.provider.products';

    protected function __construct() { }
}
