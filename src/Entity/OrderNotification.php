<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Repository\OrderNotificationRepository;

#[ORM\Entity(repositoryClass: OrderNotificationRepository::class)]
#[ORM\Table(name: "hmc_order_notifications")]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(
        name: "statusEntries",
        joinColumns: [
            new ORM\JoinColumn(name: "notification_id", referencedColumnName: "id", onDelete: "CASCADE")
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: "status_entry_id", referencedColumnName: "id", onDelete: "RESTRICT")
        ],
        joinTable: new ORM\JoinTable(
            name: "order_notification_has_status",
        ),
        inversedBy: 'orderNotifications',
        fetch: 'EXTRA_LAZY'
    ),
    new ORM\AssociationOverride(
        name: "logs",
        joinColumns: [
            new ORM\JoinColumn(name: "notification_id", referencedColumnName: "id", onDelete: "CASCADE")
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: "mapping_log_id", referencedColumnName: "id", onDelete: "RESTRICT")
        ],
        joinTable: new ORM\JoinTable(
            name: "order_notification_has_logs",
        ),
        inversedBy: 'orderMappingLogs',
        fetch: 'EXTRA_LAZY'
    ),
])]
class OrderNotification extends MappingNotification
{

    #[ORM\Column(name: "event_type", type: Types::STRING, length: 50, nullable: true)]
    /* used for event subtypes on update order use : Horeca\MiddlewareCommonLib\Constants\ShoppingCartUpdateEvents  */
    private string $eventType;

    //will be required next version
    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): void
    {
        $this->eventType = $eventType;
    }


}
