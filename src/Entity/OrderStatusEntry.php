<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="hmc_order_status_entries",
 *     indexes={
 *          @ORM\Index(name="hmc_status_entries_created_at_idx", columns={"created_at"}),
 *          @ORM\Index(name="hmc_status_entries_created_at_status_idx", columns={"created_at", "status"})
 *     })
 */
class OrderStatusEntry extends AbstractEntity
{
    /**
     * @ORM\Column(name="status", type="string", nullable=false, length=50)
     */
    protected ?string $status = null;

    /**
     * @ORM\ManyToOne(targetEntity="Horeca\MiddlewareClientBundle\Entity\OrderNotification", cascade={"persist"}, inversedBy="statusEntries")
     * @ORM\JoinColumn(name="order_notification_id", nullable=false, onDelete="CASCADE")
     * @Serializer\Exclude()
     */
    protected ?OrderNotification $order = null;

    public function __construct(?OrderNotification $order = null, ?string $status = null)
    {
        parent::__construct();
        $this->order = $order;
        $this->status = $status;
        $this->createdAt = new \DateTime();
    }

    public function toString(): string
    {
        return (string) $this->status;
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
