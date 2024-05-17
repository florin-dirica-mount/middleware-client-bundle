<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Repository\MenuNotificationRepository;

#[ORM\Entity(repositoryClass: MenuNotificationRepository::class)]
#[ORM\Table(name: "hmc_menu_notifications")]
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
            name: "menu_notification_has_status",
        )
    )
])]
class MenuNotification extends MappingNotification
{


}
