<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Horeca\MiddlewareClientBundle\Entity\Log\MappingLog;
use Horeca\MiddlewareClientBundle\Entity\MappingNotification;
use Horeca\MiddlewareClientBundle\Entity\MenuNotification;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\ProductNotification;
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
    public function saveTo(MappingNotification $notification, string $action): bool
    {
        $joinTableName = null;

        // todo: check if table name can be read from doctrine metadata
        if ($notification instanceof OrderNotification) {
            $joinTableName = 'order_notification_has_logs';
        }
        if ($notification instanceof ProductNotification) {
            $joinTableName = 'product_notification_has_logs';
        }
        if ($notification instanceof MenuNotification) {
            $joinTableName = 'menu_notification_has_logs';
        }

        if (empty($this->buffer)) {
            return false;
        }

        $sql = "INSERT INTO hmc_mapping_logs (id, action, micro_time, level, log, created_at) 
VALUES (nextval('hmc_mapping_logs_id_seq'), :action, :micro_time, :level, :log, :created_at)";
        $params = [
            'action'     => $action,
            'micro_time' => microtime(true),
            'level' => MappingLog::LEVEL_INFO,
            'log'        => implode("\n", $this->buffer),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $joinTableSql = "INSERT INTO $joinTableName (notification_id, mapping_log_id) values (:notification_id,:mapping_log_id)";

        $joinTableParams = [
            'notification_id' => $notification->getId(),
            'mapping_log_id'  => null,
        ];

        try {
            $connection = $this->entityManager->getConnection();
            try {

                $connection->beginTransaction();
                $result = $connection->executeQuery($sql, $params);

                // Get the last inserted ID
                $idQuery = "SELECT currval('hmc_mapping_logs_id_seq')";
                $stmt = $connection->executeQuery($idQuery);
                $newId = $stmt->fetchOne();

                $joinTableParams['mapping_log_id'] = $newId;

                $connection->executeQuery($joinTableSql, $joinTableParams);

                $connection->commit();
                $this->buffer = [];

                return true;
            } catch (Exception $e) {
                $connection->rollBack();
                $this->logger->info(sprintf('[%s.%d] Error: %s', __METHOD__, __LINE__, $e->getMessage()));
                return false;
            }
        } catch (\Exception) {
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
