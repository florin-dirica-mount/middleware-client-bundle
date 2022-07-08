<?php

namespace Horeca\MiddlewareClientBundle\DependencyInjection\Framework;

use Doctrine\ORM\EntityManagerInterface;

trait EntityManagerDI
{
    protected EntityManagerInterface $entityManager;

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
