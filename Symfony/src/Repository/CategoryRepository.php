<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public const FIELDS = [
        'id'   => 'EQUAL',
        'name' => 'LIKE',
    ];

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @param Category $entity
     * @param bool $flush
     * @return void
     */
    public function save(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Category $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param array $criteria
     * @return array
     */
    public function getCategories(array $criteria = []): array
    {
        $queryBuilder = $this->createQueryBuilder('categories');
        $queryBuilder = $this->filterData($queryBuilder, $criteria);

        $page = (int)($criteria['page'] ?? 1);
        $itemsPerPage = (int)($criteria['itemsPerPage'] ?? 10);

        return $queryBuilder
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $criteria
     * @return QueryBuilder
     */
    public function filterData(QueryBuilder $queryBuilder, array $criteria = []): QueryBuilder
    {
        $alias = $queryBuilder->getRootAliases()[0];

        foreach (self::FIELDS as $key => $type) {
            if (!isset($criteria[$key]) || $criteria[$key] === '') {
                continue;
            }

            if ($type === 'LIKE') {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->like("$alias.$key", ":$key"))
                    ->setParameter($key, '%' . $criteria[$key] . '%');
            } elseif ($type === 'EQUAL') {
                $queryBuilder
                    ->andWhere("$alias.$key = :$key")
                    ->setParameter($key, $criteria[$key]);
            }
        }

        return $queryBuilder;
    }
}