<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Repository\OrderNotificationRepository;

#[ORM\Entity(repositoryClass: OrderNotificationRepository::class)]
#[ORM\Table(name: "hmc_order_notifications")]
class OrderNotification extends MappingNotification
{


}
