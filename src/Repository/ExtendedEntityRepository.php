<?php
namespace Horeca\MiddlewareClientBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

abstract class ExtendedEntityRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry, string $entityClass = null)
    {
        if ($entityClass) {
            parent::__construct($registry, $entityClass);
        } else {
            parent::__construct($registry, $this->getEntityNameFromRepositoryName());
        }
    }

    public function getEntityNameFromRepositoryName(): string
    {
        $class = \get_class($this);
        return (string) \str_replace(['Horeca\\MiddlewareClientBundle\\Repository\\', 'Repository'], ['Horeca\\MiddlewareClientBundle\\Entity\\', ''], $class);
    }

    /**
     * Executes a DDL query and returns the first object returned by that query
     * @param string $query The DDL query to execute
     * @param array $params The params for the DDL query
     * @return mixed
     * @throws NonUniqueResultException
     */
    protected function getOne($query, $params = [])
    {
        /** @var $q Query */
        $q = $this->getEntityManager()->createQuery($query);
        $q->setParameters($params);
        $q->setMaxResults(1);
        try {
            $res = $q->getSingleResult();
            return $res;
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * Executes a DDL query and returns an array with all the objects returned
     * @param string $query The DDL query to execute
     * @param array $params The params for the DDL query
     * @param int $limit The maximum number of results to return
     * @param int $offset The offset to start from when returning results
     * @return array
     * @throws \Exception
     */
    protected function getAll($query, $params = [], $limit = 0, $offset = 0)
    {
        /** @var $q Query */
        $q = $this->getEntityManager()->createQuery($query);
        $q->setParameters($params);
        if ($limit) $q->setMaxResults($limit);
        if ($offset) $q->setFirstResult($offset);
        return $q->getResult();
    }

    /**
     * @throws \Exception
     */
    public function fetchOne(string $sql, array $params = []): mixed
    {
        return $this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql, $params)
            ->fetchAssociative();
    }

    /**
     * @throws \Exception
     */
    public function fetchOneAsValue(string $sql, array $params = [])
    {
        $result = $this->fetchOne($sql, $params);
        if (!empty($result) && is_array($result)) {
            return reset($result);
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql, $params)
            ->fetchAssociative();
    }

    /**
     * @throws \Exception
     */
    public function executeSql(string $sql, array $params = []): int
    {
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);

        return $stmt->executeQuery($params)->rowCount();
    }

    /**
     * Persist an object to the database
     * @param $object
     * @throws ORMException
     */
    public function persist($object): void
    {
        $this->getEntityManager()->persist($object);
    }

    /**
     * Remove an object from the database
     * @param $object
     * @throws ORMException
     */
    public function remove($object): void
    {
        $this->getEntityManager()->remove($object);
    }

    /**
     * Refresh the object by reading new data from the database
     * @param $object
     * @throws ORMException
     */
    public function refresh($object): void
    {
        $this->getEntityManager()->refresh($object);
    }

    /**
     * Detach the object from the entity manager
     * @param $object
     * @throws \Exception
     */
    public function detach($object): void
    {
        $this->getEntityManager()->detach($object);
    }

    /**
     * Start a database transaction
     */
    public function begin(): void
    {
        $this->getEntityManager()->getConnection()->beginTransaction();
    }

    /**
     * Commit the database transaction
     */
    public function commit(): void
    {
        $this->getEntityManager()->getConnection()->commit();
    }

    /**
     * Rollback the database transaction
     */
    public function rollback(): void
    {
        $this->getEntityManager()->getConnection()->rollback();
    }

    /**
     * @throws \Exception
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->_em;
    }

    /**
     * @param string $column
     * @param array $arr
     * @return string
     */
    protected function sql_and_in(string $column, array $arr): string
    {
        $values = array_map(function ($val) {
            if (is_int($val)) {
                return $val;
            }
            return sprintf("'%s'", $val);
        }, $arr);

        return sprintf(' AND %s IN (%s)', $column, implode(", ", $values));
    }

    /**
     * @param string $column
     * @param string $inclusion IN or NOT IN
     * @param array $values
     * @return string
     */
    public function sql_in(string $column, string $inclusion, array $values): string
    {
        $values = array_map(function ($val) {
            if (is_int($val)) {
                return $val;
            }
            return sprintf("'%s'", $val);
        }, $values);
        $inclusion = in_array($inclusion, ['IN', 'NOT IN']) ? $inclusion : 'IN';

        return sprintf(' %s %s (%s)', $column, $inclusion, implode(", ", $values));
    }

    /**
     * Builds the where clause for containing a value in a json_value column.
     * Joins the query parts with "OR" by default, add another parameter if "AND" condition is needed
     * @param string $column
     * @param array $values
     * @return string
     */
    protected function sql_build_json_contains(string $column, array $values): string
    {
        $parts = [];
        foreach ($values as $value) {
            $parts[] = "JSON_CONTAINS($column, '{$value}')";
        }

        return implode(' OR ', $parts);
    }
}
