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
            new ORM\JoinColumn(name: "notification_id", referencedColumnName: "id", onDelete: "CASCADE")
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: "status_entry_id", referencedColumnName: "id", onDelete: "RESTRICT")
        ],
        joinTable: new ORM\JoinTable(
            name: "menu_notification_has_status",
        ),
        inversedBy: 'menuNotifications',
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
            name: "menu_notification_has_logs",
        ),
        inversedBy: 'menuMappingLogs',
        fetch: 'EXTRA_LAZY',
    ),
])]
class MenuNotification extends MappingNotification
{



}
