<?php

namespace Horeca\MiddlewareClientBundle\Service;

use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\EntityManagerDI;
use Horeca\MiddlewareClientBundle\DependencyInjection\Framework\LoggerDI;
use Horeca\MiddlewareClientBundle\Entity\Log\OrderLog;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;

class OrderLogger
{
    use EntityManagerDI;
    use LoggerDI;

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

        $this->info(__METHOD__, __LINE__, sprintf('Memory usage: %s %s', $memory, $unit));
    }

    public function saveTo(OrderNotification $order, string $action): void
    {
        if (empty($this->buffer)) {
            return;
        }

        $log = new OrderLog(OrderLog::LEVEL_INFO, implode("\n", $this->buffer));
        $log->setAction($action);
        $order->addLog($log);

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        $this->buffer = [];
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