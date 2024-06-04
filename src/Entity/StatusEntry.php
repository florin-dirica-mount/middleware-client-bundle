<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Enum\SerializationGroups;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity]
#[ORM\Table(name: "hmc_status_entries")]
#[ORM\Index(columns: ["created_at"])]
#[ORM\Index(columns: ["created_at", "status"])]
class StatusEntry
{
    #[ORM\Id]
    #[ORM\Column(name: "id", type: "integer")]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Serializer\Groups([SerializationGroups::Default, SerializationGroups::TenantOrderNotificationView])]
    protected int $id;

    #[ORM\Column(name: "status", type: "string", length: 50, nullable: false)]
    protected ?string $status = null;

    #[ORM\Column(name: "created_at", type: "datetime", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected \DateTime $createdAt;

    public function __construct(?OrderNotification $order = null, ?string $status = null)
    {
        $this->status = $status;
        $this->createdAt = new \DateTime();
    }

    public function __toString(): string
    {
        return sprintf('%s - [%s]', $this->status, $this->createdAt->format('d-m-Y H:i:s'));
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }


}
