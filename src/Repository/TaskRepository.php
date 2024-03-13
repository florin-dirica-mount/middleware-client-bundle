<?php

namespace Horeca\MiddlewareClientBundle\Repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Horeca\MiddlewareClientBundle\Entity\Task;
use Horeca\MiddlewareClientBundle\Enum\TaskType;


/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]|array findAll()
 * @method Task[]|array findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findRunningTaskForIdentifier($identifier): ?Task
    {
        return $this->createQueryBuilder('t')
            ->select('t')
            ->where('t.identifier = :identifier')
            ->andWhere('t.status  in ( :statuses )')
            ->setParameter('identifier', $identifier)
            ->setParameter('statuses', [TaskType::STATUS_QUEUED, TaskType::STATUS_RUNNING])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }


}
