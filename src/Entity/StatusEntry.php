<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity]
#[ORM\Table(name: "hmc_status_entries")]
#[ORM\Index(columns: ["created_at"])]
#[ORM\Index(columns: ["created_at", "status"])]
class StatusEntry extends DefaultEntity
{
    #[ORM\Column(name: "status", type: "string", length: 50, nullable: false)]
    protected ?string $status = null;


    public function __construct(?OrderNotification $order = null, ?string $status = null)
    {
        parent::__construct();
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


}
