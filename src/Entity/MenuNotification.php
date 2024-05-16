<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Repository\MenuNotificationRepository;

#[ORM\Entity(repositoryClass: MenuNotificationRepository::class)]
#[ORM\Table(name: "hmc_menu_notifications")]
class MenuNotification extends MappingNotification
{


}
