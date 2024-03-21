<?php

namespace Horeca\MiddlewareClientBundle\Repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Horeca\MiddlewareClientBundle\Entity\AbstractTask;
use Horeca\MiddlewareClientBundle\Enum\TaskStatus;


/**
 * @method AbstractTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractTask[]|array findAll()
 * @method AbstractTask[]|array findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class AbstractTaskRepository extends ServiceEntityRepository
{

    public function findRunningTaskForIdentifier($identifier): ?AbstractTask
    {
        return $this->createQueryBuilder('t')
            ->select('t')
            ->where('t.identifier = :identifier')
            ->andWhere('t.status  in ( :statuses )')
            ->setParameter('identifier', $identifier)
            ->setParameter('statuses', [TaskStatus::STATUS_QUEUED, TaskStatus::STATUS_RUNNING])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }


}
