<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\Entity\Log\OrderLog;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Psr\Log\LoggerInterface;

class OrderLogger
{

    public function __construct(protected EntityManagerInterface $entityManager,
                                protected LoggerInterface        $logger)
    {
    }

    private array $buffer = [];

    public function debug(string $method, int $line, string|array|null $log = null): void
    {
        $this->buffer[] = $this->format(OrderLog::LEVEL_DEBUG, $method, $line, $log);
    }

    public function info(string $method, int $line, string|array|null $log = null): void
    {
        $this->buffer[] = $this->format(OrderLog::LEVEL_INFO, $method, $line, $log);
    }

    public function error(string $method, int $line, string|array|null $log = null): void
    {
        $this->buffer[] = $this->format(OrderLog::LEVEL_ERROR, $method, $line, $log);
    }

    public function warning(string $method, int $line, string|array|null $log = null): void
    {
        $this->buffer[] = $this->format(OrderLog::LEVEL_WARNING, $method, $line, $log);
    }

    public function critical(string $method, int $line, string|array|null $log = null): void
    {
        $this->buffer[] = $this->format(OrderLog::LEVEL_CRITICAL, $method, $line, $log);
    }

    public function logMemoryUsage(): void
    {
        $memory = round(memory_get_usage() / 1024 / 1024, 2);

        $this->info(__METHOD__, __LINE__, "Memory: $memory MB");
    }

    public function saveTo(OrderNotification $order, string $action): bool
    {
        if (empty($this->buffer)) {
            return false;
        }

        $sql = "INSERT INTO hmc_order_logs (id, action, order_id, micro_time, level, log, created_at) 
VALUES (nextval('hmc_order_logs_id_seq'), :action, :order_id, :micro_time, :level, :log, :created_at)";
        $params = [
            'action'     => $action,
            'order_id'   => $order->getId(),
            'micro_time' => microtime(true),
            'level'      => OrderLog::LEVEL_INFO,
            'log'        => implode("\n", $this->buffer),
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $this->entityManager->getConnection()->executeQuery($sql, $params);
            $this->buffer = [];

            return true;
        } catch (Exception $e) {
            $this->logger->info(sprintf('[%s.%d] Error: %s', __METHOD__, __LINE__, $e->getMessage()));

            return false;
        }
    }

    private function format(string $level, string $method, int $line, string|array|null $log = null): string
    {
        $methodName = substr($method, strrpos($method, '\\') + 1);
        $data = is_array($log) ? json_encode($log) : $log;
        $text = sprintf('[%s][%s.%d] %s %s',
            date('Y-m-d H:i:s.v'),
            $methodName,
            $line,
            $level,
            $data
        );

        $this->logger->info($text);

        return $text;
    }
}