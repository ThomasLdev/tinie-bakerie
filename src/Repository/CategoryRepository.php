<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
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

    /**
     * @return array<array-key,mixed>
     */
    #[\Override]
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
            ->orderBy('c.createdAt', 'DESC');

        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getResult();

        return \is_array($result) ? $result : [];
    }

    /**
     * @return array<array-key,mixed>
     */
    public function findAllSlugs(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug}')
            ->orderBy('c.createdAt', 'DESC');

        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getResult();

        return \is_array($result) ? $result : [];
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

        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getOneOrNullResult();

        return $result instanceof Category ? $result : null;
    }

    /**
     * Find a category by ID with all related data.
     * Uses HINT_REFRESH to ensure locale filter is applied.
     */
    public function findOneById(int $id): ?Category
    {
        $qb = $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id, createdAt, updatedAt}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug, description, metaDescription, metaTitle, excerpt}')
            ->leftJoin('c.media', 'm')
            ->addSelect('PARTIAL m.{id, mediaName, type}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, title, alt}')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getOneOrNullResult();

        return $result instanceof Category ? $result : null;
    }
}
