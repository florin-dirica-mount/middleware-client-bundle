<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\ProtocolActionsService;

trait ProtocolActionsServiceDI
{
    protected ProtocolActionsService $protocolActionsService;

    /**
     * @required
     */
    public function setProtocolActionsService(ProtocolActionsService $protocolActionsService): void
    {
        $this->protocolActionsService = $protocolActionsService;
    }
}
