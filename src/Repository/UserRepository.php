<?php

namespace Horeca\MiddlewareClientBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Horeca\MiddlewareClientBundle\Entity\User;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]|array findAll()
 * @method User[]|array findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ExtendedEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

}
