<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Repository\ProductNotificationRepository;

#[ORM\Entity(repositoryClass: ProductNotificationRepository::class)]
#[ORM\Table(name: "hmc_product_notifications")]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(
        name: "statusEntries",
        joinColumns: [
            new ORM\JoinColumn(name: "status_entry_id", referencedColumnName: "id", onDelete: "RESTRICT")
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: "notification_id", referencedColumnName: "id", onDelete: "CASCADE")
        ],
        joinTable: new ORM\JoinTable(
            name: "product_notification_has_status",
        )
    ),
    new ORM\AssociationOverride(
        name: "logs",
        joinColumns: [
            new ORM\JoinColumn(name: "mapping_log_id", referencedColumnName: "id", onDelete: "RESTRICT")
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: "notification_id", referencedColumnName: "id", onDelete: "CASCADE")
        ],
        joinTable: new ORM\JoinTable(
            name: "product_notification_has_logs",
        )
    ),
])]
class ProductNotification extends MappingNotification
{


}
