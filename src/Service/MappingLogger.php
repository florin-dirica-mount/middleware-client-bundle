<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\Entity\BaseProviderCredentials;
use Horeca\MiddlewareClientBundle\Entity\Log\MappingLog;
use Horeca\MiddlewareClientBundle\Entity\Log\OrderLog;
use Horeca\MiddlewareClientBundle\Entity\MappingNotification;
use Horeca\MiddlewareClientBundle\Entity\MenuNotification;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\ProductNotification;
use Horeca\MiddlewareClientBundle\Exception\MiddlewareClientException;
use Psr\Log\LoggerInterface;


class MappingLogger
{

    public function __construct(protected EntityManagerInterface $entityManager,
                                protected LoggerInterface        $logger)
    {
    }

    private array $buffer = [];

    public function debug(string $method, int $line, string|array|null $log = null): void
    {
        $this->buffer[] = $this->format(MappingLog::LEVEL_DEBUG, $method, $line, $log);
    }

    public function info(string $method, int $line, string|array|null $log = null): void
    {
        $this->buffer[] = $this->format(MappingLog::LEVEL_INFO, $method, $line, $log);
    }

    public function error(string $method, int $line, string|array|null $log = null): void
    {
        $this->buffer[] = $this->format(MappingLog::LEVEL_ERROR, $method, $line, $log);
    }

    public function warning(string $method, int $line, string|array|null $log = null): void
    {
        $this->buffer[] = $this->format(MappingLog::LEVEL_WARNING, $method, $line, $log);
    }

    public function critical(string $method, int $line, string|array|null $log = null): void
    {
        $this->buffer[] = $this->format(MappingLog::LEVEL_CRITICAL, $method, $line, $log);
    }

    public function logMemoryUsage(): void
    {
        $memory = round(memory_get_usage() / 1024 / 1024, 2);

        $this->info(__METHOD__, __LINE__, "Memory: $memory MB");
    }

    /**
     * @param OrderNotification|ProductNotification|MenuNotification $notification
     * @param string $action
     * @return bool
     */
    public function saveTo(OrderNotification|ProductNotification|MenuNotification $notification, string $action): bool
    {
        $tableName = null;
        $joinTableName = null;

        if ($notification instanceof OrderNotification) {
            $tableName = 'hmc_order_notifications';
            $joinTableName = 'order_notification_has_status';
        }
        if ($notification instanceof ProductNotification) {
            $tableName = 'hmc_product_notifications';
            $joinTableName = 'hmc_product_notifications';
        }
        if ($notification instanceof MenuNotification) {
            $tableName = 'hmc_menu_notifications';
            $joinTableName = 'hmc_menu_notifications';
        }


        if (empty($this->buffer) || !$tableName) {
            return false;
        }

        $sql = "INSERT INTO $tableName (id, action, micro_time, level, log, created_at) 
VALUES (nextval('hmc_mapping_logs_id_seq'), :action, :micro_time, :level, :log, :created_at)";
        $params = [
            'action'     => $action,
            'micro_time' => microtime(true),
            'level'      => OrderLog::LEVEL_INFO,
            'log'        => implode("\n", $this->buffer),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $joinTableSql = "INSERT INTO $joinTableName (notification_id, mapping_log_id) values (:notification_id,mapping_log_id)";

        $joinTableParams = [
            'notification_id' => 1,
            'mapping_log_id'  => 1,
        ];

        try {
            $connection = $this->entityManager->getConnection();

            $connection->beginTransaction();
            dd($connection->executeQuery($sql, $params));
            $connection->executeQuery($joinTableSql, $joinTableParams);

            $connection->commit();
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
