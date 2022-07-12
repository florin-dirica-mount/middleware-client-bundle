<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\Entity\Log\RequestLog;
use Horeca\MiddlewareClientBundle\Repository\Log\RequestLogRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestService
{
    private ?float $requestTime = null;

    private LoggerInterface      $logger;
    private RequestLogRepository $requestLogRepository;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->requestLogRepository = $entityManager->getRepository(RequestLog::class);
    }

    public function init(Request $request): void
    {
        $this->requestTime = $this->getMicrotime();
    }

    public function createRequestLog(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        try {
            $log = new RequestLog();
            $log->setMethod($request->getMethod());
            $log->setUri($request->getPathInfo());
            $log->setHeaders(json_encode($request->headers->all()));
            $log->setQueryParams(json_encode($request->query->all()));
            $log->setBodyParams(json_encode($request->request->all()));
            $log->setStatusCode($response->getStatusCode());
            $log->setRequestDuration($this->getMicroTime() - $this->requestTime);

            if (in_array('application/json', $response->headers->all())) {
                $log->setResponseBody($response->getContent());
            }

            if ($exception) {
                $log->setException($exception->getMessage()
                    . PHP_EOL
                    . $exception->getTraceAsString());
            }

            $this->requestLogRepository->insert($log);
        } catch (\Exception $e) {
            $this->logger->critical('[createRequestLog] ' . $e->getMessage());
            $this->logger->critical('[createRequestLog] ' . $e->getTraceAsString());
        }
    }

    public function isLoggableStatusCode(int $statusCode): bool
    {
        return in_array($statusCode, [400, 401, 403, 422, 500]);
    }

    private function getMicroTime(): float
    {
        return microtime(true);
    }
}
