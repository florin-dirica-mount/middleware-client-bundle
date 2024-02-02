<?php

namespace Horeca\MiddlewareClientBundle\Enum;

final class OrderNotificationType
{
    const NewOrder = 'new-order';
    const OrderUpdate = 'order-update';

    protected function __construct() { }
}