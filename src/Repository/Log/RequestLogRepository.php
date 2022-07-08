<?php

namespace Horeca\MiddlewareClientBundle\Repository\Log;

use Horeca\MiddlewareClientBundle\Entity\Log\RequestLog;
use Horeca\MiddlewareClientBundle\Repository\ExtendedEntityRepository;

/**
 * @method RequestLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method RequestLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method RequestLog[]|array findAll()
 * @method RequestLog[]|array findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestLogRepository extends ExtendedEntityRepository
{

    /**
     * @throws \Exception
     */
    public function insert(RequestLog $log): int
    {
        $sql = "INSERT INTO request_logs (id, method, uri, status_code, headers, query_params, body_params, response_body, exception, request_duration)
VALUES (UUID(), :method, :uri, :status_code, :headers, :query_params, :body_params, :response_body, :exception, :request_duration)";

        $params = [
            'method'           => $log->getMethod(),
            'uri'              => $log->getUri(),
            'status_code'      => $log->getStatusCode(),
            'headers'          => $log->getHeaders(),
            'query_params'     => $log->getQueryParams(),
            'body_params'      => $log->getBodyParams(),
            'response_body'    => $log->getResponseBody(),
            'exception'        => $log->getException(),
            'request_duration' => $log->getRequestDuration()
        ];

        return $this->executeSql($sql, $params);
    }
}
