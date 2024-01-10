<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\OrderLogger;
use Symfony\Contracts\Service\Attribute\Required;

trait OrderLoggerDI
{
    protected OrderLogger $orderLogger;

    #[Required]
    public function setOrderLogger(OrderLogger $orderLogger): void
    {
        $this->orderLogger = $orderLogger;
    }
}