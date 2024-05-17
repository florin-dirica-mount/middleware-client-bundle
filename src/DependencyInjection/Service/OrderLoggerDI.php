<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\OrderLogger;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @deprecated
 */
trait OrderLoggerDI
{
    protected OrderLogger $orderLogger;

    /**
     * @required
     */
    public function setOrderLogger(OrderLogger $orderLogger): void
    {
        $this->orderLogger = $orderLogger;
    }
}
