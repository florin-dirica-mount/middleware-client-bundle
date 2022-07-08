<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Service;

use Horeca\MiddlewareClientBundle\Service\RequestService;

trait RequestServiceDI
{
    protected RequestService $requestService;

    /**
     * @required
     */
    public function setRequestService(RequestService $requestService): void
    {
        $this->requestService = $requestService;
    }
}
