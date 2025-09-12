<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id, createdAt, updatedAt}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug}')
            ->leftJoin('c.media', 'm')
            ->addSelect('PARTIAL m.{id, mediaName, type}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, title, alt}')
            ->orderBy('c.createdAt', 'DESC')
        ;

        $result = $qb->getQuery()->getResult();

        return is_array($result) ? $result : [];
    }

    public function findOne(string $slug): ?Category
    {
        $qb = $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id, createdAt, updatedAt}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug, description, metaDescription, metaTitle, excerpt}')
            ->leftJoin('c.media', 'm')
            ->addSelect('PARTIAL m.{id, mediaName, type}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, title, alt}')
            ->where('ct.slug = :slug')
            ->setParameter('slug', $slug)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result instanceof Category ? $result : null;
    }
}
