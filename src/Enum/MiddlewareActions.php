<?php

namespace Horeca\MiddlewareClientBundle\Enum;

class MiddlewareActions
{
    public const SyncTenantProducts = 'middleware.sync.tenant.products';
    public const SyncProviderProducts = 'middleware.sync.provider.products';

    public const ExportProviderProducts = 'middleware.export.provider.products';
    public const UpdateProviderProducts = 'middleware.update.provider.products';
    public const SyncAndExportProviderProducts = 'middleware.sync.export.provider.products';

    protected function __construct() { }
}
