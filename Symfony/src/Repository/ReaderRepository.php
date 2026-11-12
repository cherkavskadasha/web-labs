<?php

namespace App\Repository;

use App\Entity\Reader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reader>
 */
class ReaderRepository extends ServiceEntityRepository
{
    public const FIELDS = [
        'id'        => 'EQUAL',
        'full_name' => 'LIKE',
        'email'     => 'LIKE',
    ];

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reader::class);
    }

    /**
     * @param Reader $entity
     * @param bool $flush
     * @return void
     */
    public function save(Reader $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Reader $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Reader $entity, bool $flush = false): void
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
    public function getReaders(array $criteria = []): array
    {
        $queryBuilder = $this->createQueryBuilder('readers');
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

        $fieldMap = [
            'id'        => 'id',
            'full_name' => 'fullName',
            'email'     => 'email',
        ];

        foreach (self::FIELDS as $key => $type) {
            if (!isset($criteria[$key]) || $criteria[$key] === '') {
                continue;
            }

            $entityField = $fieldMap[$key] ?? $key;

            if ($type === 'LIKE') {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->like("$alias.$entityField", ":$key"))
                    ->setParameter($key, '%' . $criteria[$key] . '%');
            } elseif ($type === 'EQUAL') {
                $queryBuilder
                    ->andWhere("$alias.$entityField = :$key")
                    ->setParameter($key, $criteria[$key]);
            }
        }

        return $queryBuilder;
    }
}