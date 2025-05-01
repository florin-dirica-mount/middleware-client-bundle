<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\ProtocolActionsService;
use Symfony\Contracts\Service\Attribute\Required;

trait ProtocolActionsServiceDI
{
    protected ProtocolActionsService $protocolActionsService;

    #[Required]
    public function setProtocolActionsService(ProtocolActionsService $protocolActionsService): void
    {
        $this->protocolActionsService = $protocolActionsService;
    }
}
