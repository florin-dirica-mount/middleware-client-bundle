<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity]
#[ORM\Table(name: "hmc_order_status_entries")]
#[ORM\Index(columns: ["created_at"], name: "hmc_order_status_entries_created_at_idx")]
#[ORM\Index(columns: ["created_at", "status"], name: "hmc_order_status_entries_created_at_status_idx")]
class OrderStatusEntry extends DefaultEntity
{
    #[ORM\Column(name: "status", type: "string", length: 50, nullable: false)]
    protected ?string $status = null;

    #[ORM\ManyToOne(targetEntity: OrderNotification::class, cascade: ["persist"], inversedBy: "statusEntries")]
    #[ORM\JoinColumn(name: "order_notification_id", nullable: false, onDelete: "CASCADE")]
    #[Serializer\Exclude]
    protected ?OrderNotification $order = null;

    public function __construct(?OrderNotification $order = null, ?string $status = null)
    {
        parent::__construct();
        $this->order = $order;
        $this->status = $status;
        $this->createdAt = new \DateTime();
    }

    public function __toString(): string
    {
        return sprintf('%s - [%s]', $this->status, $this->createdAt->format('d-m-Y H:i:s'));
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return OrderNotification|null
     */
    public function getOrder(): ?OrderNotification
    {
        return $this->order;
    }

    /**
     * @param OrderNotification|null $order
     */
    public function setOrder(?OrderNotification $order): void
    {
        $this->order = $order;
    }

}
