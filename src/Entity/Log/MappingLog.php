<?php

namespace Horeca\MiddlewareClientBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Entity\MenuNotification;
use Horeca\MiddlewareClientBundle\Entity\OrderNotification;
use Horeca\MiddlewareClientBundle\Entity\ProductNotification;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity]
#[ORM\Table(name: 'hmc_mapping_logs')]
#[ORM\Index(columns: ['created_at'], name: 'idx_hmc_mapping_logs_created_at')]
#[ORM\Index(columns: ['level'], name: 'idx_hmc_mapping_logs_level')]
#[ORM\Index(columns: ['action'], name: 'idx_hmc_mapping_logs_action')]
class MappingLog
{
    public const LEVEL_DEBUG = 'DEBUG';
    public const LEVEL_INFO = 'INFO';
    public const LEVEL_ERROR = 'ERROR';
    public const LEVEL_WARNING = 'WARNING';
    public const LEVEL_CRITICAL = 'CRITICAL';

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(name: 'action', type: 'string', length: 128, nullable: false)]
    private string $action;

//    #[ORM\ManyToOne(targetEntity: OrderNotification::class, cascade: ['persist'], inversedBy: 'logs')]
//    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
//    #[Serializer\Exclude]
//    private OrderNotification $order;

    #[ORM\OneToMany(mappedBy: 'logs', targetEntity: OrderNotification::class, orphanRemoval: true)]
    private $orderMappingLogs;
    #[ORM\OneToMany(mappedBy: 'logs', targetEntity: MenuNotification::class, orphanRemoval: true)]
    private $menuMappingLogs;
    #[ORM\OneToMany(mappedBy: 'logs', targetEntity: ProductNotification::class, orphanRemoval: true)]
    private $productMappingLogs;

    #[ORM\Column(name: 'micro_time', type: 'float', nullable: false)]
    private float $microTime;

    #[ORM\Column(name: 'level', type: 'string', length: 20, nullable: false)]
    private string $level;

    #[ORM\Column(name: 'log', type: 'text', nullable: false)]
    private string $log;

    #[ORM\Column(name: "created_at", type: "datetime", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTime $createdAt;

    public function __construct(string $level, string $log)
    {
        $this->microTime = microtime(true);
        $this->createdAt = new \DateTime();
        $this->level = $level;
        $this->log = $log;
    }

    public function __toString(): string
    {
//        return sprintf('%s: %s', $this->order->getId(), $this->level);
        return sprintf('%s: %s', $this->getId(), $this->level);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }


    public function getMicroTime(): float
    {
        return $this->microTime;
    }

    public function setMicroTime(float $microTime): void
    {
        $this->microTime = $microTime;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    public function getLog(): string
    {
        return $this->log;
    }

    public function setLog(string $log): void
    {
        $this->log = $log;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

}
