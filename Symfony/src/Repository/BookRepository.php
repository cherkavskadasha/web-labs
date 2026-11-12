<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public const FIELDS = [
        'id'             => 'EQUAL',
        'title'          => 'LIKE',
        'published_year' => 'BETWEEN',
        'author_id'      => 'EQUAL',
        'category_id'    => 'EQUAL',
    ];

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @param Book $entity
     * @param bool $flush
     * @return void
     */
    public function save(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Book $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Book $entity, bool $flush = false): void
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
    public function getBooks(array $criteria = []): array
    {
        $queryBuilder = $this->createQueryBuilder('books');
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
            'id'             => 'id',
            'title'          => 'title',
            'published_year' => 'publishedYear',
            'author_id'      => 'author',
            'category_id'    => 'category',
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
            } elseif ($type === 'BETWEEN') {
                if (is_array($criteria[$key])) {
                    if (!empty($criteria[$key]['gte'])) {
                        $queryBuilder
                            ->andWhere("$alias.$entityField >= :{$key}Gte")
                            ->setParameter("{$key}Gte", $criteria[$key]['gte']);
                    }
                    if (!empty($criteria[$key]['lte'])) {
                        $queryBuilder
                            ->andWhere("$alias.$entityField <= :{$key}Lte")
                            ->setParameter("{$key}Lte", $criteria[$key]['lte']);
                    }
                } else {
                    $queryBuilder
                        ->andWhere("$alias.$entityField = :$key")
                        ->setParameter($key, $criteria[$key]);
                }
            }
        }

        return $queryBuilder;
    }
}