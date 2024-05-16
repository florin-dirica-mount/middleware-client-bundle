<?php

namespace Horeca\MiddlewareClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Repository\ProductNotificationRepository;

#[ORM\Entity(repositoryClass: ProductNotificationRepository::class)]
#[ORM\Table(name: "hmc_product_notifications")]
class ProductNotification extends MappingNotification
{


}
