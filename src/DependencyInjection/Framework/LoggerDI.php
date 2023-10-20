<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Framework;

use Psr\Log\LoggerInterface;

trait LoggerDI
{
    protected LoggerInterface $logger;

    /**
     * @required
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
