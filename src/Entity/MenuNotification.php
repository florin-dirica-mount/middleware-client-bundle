<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Entity\Log\MappingLog;
use Horeca\MiddlewareClientBundle\Repository\MenuNotificationRepository;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: MenuNotificationRepository::class)]
#[ORM\Table(name: "hmc_menu_notifications")]
//#[ORM\AssociationOverrides([
//    new ORM\AssociationOverride(
//        name: "statusEntries",
//        joinColumns: [
//            new ORM\JoinColumn(name: "notification_id", referencedColumnName: "id", onDelete: "CASCADE")
//        ],
//        inverseJoinColumns: [
//            new ORM\JoinColumn(name: "status_entry_id", referencedColumnName: "id", onDelete: "RESTRICT")
//        ],
//        joinTable: new ORM\JoinTable(
//            name: "menu_notification_has_status",
//        ),
//        inversedBy: 'menuNotifications',
//        fetch: 'EXTRA_LAZY'
//    ),
//    new ORM\AssociationOverride(
//        name: "logs",
//        joinColumns: [
//            new ORM\JoinColumn(name: "notification_id", referencedColumnName: "id", onDelete: "CASCADE")
//        ],
//        inverseJoinColumns: [
//            new ORM\JoinColumn(name: "mapping_log_id", referencedColumnName: "id", onDelete: "RESTRICT")
//        ],
//        joinTable: new ORM\JoinTable(
//            name: "menu_notification_has_logs",
//        ),
//        inversedBy: 'menuMappingLogs',
//        fetch: 'EXTRA_LAZY',
//    ),
//])]
class MenuNotification extends MappingNotification
{


    /**
     * @var Collection<int, MappingLog>|MappingLog[]
     */
    #[ORM\ManyToMany(targetEntity: MappingLog::class, cascade: ["persist"], fetch: "EXTRA_LAZY")]
    #[ORM\JoinTable(name: 'menu_notification_has_logs')]
    #[ORM\JoinColumn(name: 'notification_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'mapping_log_id', referencedColumnName: 'id', onDelete: 'RESTRICT')]
    #[ORM\OrderBy(["createdAt" => "ASC"])]
    #[Serializer\Exclude]
    private Collection|array $logs;

    /**
     * @var StatusEntry[] |Collection<int, StatusEntry>
     */
    #[ORM\ManyToMany(targetEntity: StatusEntry::class, cascade: ["persist"], fetch: "EXTRA_LAZY", orphanRemoval: true)]
    #[ORM\JoinTable(name: 'menu_notification_has_status')]
    #[ORM\JoinColumn(name: 'notification_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'status_entry_id', referencedColumnName: 'id', onDelete: 'RESTRICT')]
    #[ORM\OrderBy(["createdAt" => "ASC"])]
    #[Serializer\Exclude]
    private Collection|array $statusEntries;

}
