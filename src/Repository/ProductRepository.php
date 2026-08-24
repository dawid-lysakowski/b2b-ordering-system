<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findFiltered(
        ?string $search,
        ?Category $category,
        ?float $minPrice,
        ?float $maxPrice,
        bool $onlyActive = true
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c');

        if ($onlyActive) {
            $qb
                ->andWhere('p.isActive = true')
                ->andWhere('c.isActive = true');
        }

        if ($search) {
            $qb
                ->andWhere('(LOWER(p.name) LIKE LOWER(:search) OR LOWER(p.sku) LIKE LOWER(:search))')
                ->setParameter('search', '%' . trim($search) . '%');
        }

        if ($category) {
            $qb
                ->andWhere('p.category = :category')
                ->setParameter('category', $category);
        }

        if ($minPrice !== null) {
            $qb
                ->andWhere('p.priceNet >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice !== null) {
            $qb
                ->andWhere('p.priceNet <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        return $qb
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
