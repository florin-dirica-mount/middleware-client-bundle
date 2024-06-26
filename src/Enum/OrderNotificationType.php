<?php

namespace Horeca\MiddlewareClientBundle\Enum;

final class OrderNotificationType
{
    const NewOrder = 'new-order';
    const OrderUpdate = 'order-update';
    const Retry = 'retry';

    protected function __construct() { }
}
