<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Repository;

use Horeca\MiddlewareClientBundle\Repository\Log\RequestLogRepository;

trait RequestLogRepositoryDI
{
    protected RequestLogRepository $requestLogRepository;

    /**
     * @required
     */
    public function setRequestLogRepository(RequestLogRepository $requestLogRepository): void
    {
        $this->requestLogRepository = $requestLogRepository;
    }
}
