<?php

namespace Horeca\MiddlewareClientBundle\Enum;

final class OrderTransferMode
{
    public const ASYNC = 'async';
    public const SYNC = 'sync';

    private function __construct() { }
}