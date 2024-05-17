<?php

namespace Horeca\MiddlewareClientBundle\Enum;

final class ProductNotificationType
{
    const ProductAdd = 'product-add';
    const ProductBulkAdd = 'product-bulk-add';
    const ProductUpdate = 'product-update';
    const ProductBulkUpdate = 'product-bulk-update';

    protected function __construct() { }
}
