<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\Entity\Log\RequestLog;
use Horeca\MiddlewareClientBundle\Repository\Log\RequestLogRepository;

trait RequestLogRepositoryDI
{
    protected RequestLogRepository $requestLogRepository;

    /**
     * @required
     */
    public function setRequestLogRepository(EntityManagerInterface $entityManager): void
    {
        $this->requestLogRepository = $entityManager->getRepository(RequestLog::class);
    }
}
