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
    public function findAllSlugs(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug, locale}')
            ->orderBy('c.createdAt', 'DESC');

        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getResult();

        return \is_array($result) ? $result : [];
    }

    /**
     * @return list<Category>
     */
    public function findFeatured(int $limit = 5): array
    {
        $idsResult = $this->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.isFeatured = :featured')
            ->setParameter('featured', true)
            ->orderBy('c.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        $ids = array_column($idsResult, 'id');

        if ([] === $ids) {
            return [];
        }

        $qb = $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id, createdAt, updatedAt}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug, locale}')
            ->leftJoin('c.media', 'm')
            ->addSelect('PARTIAL m.{id, media, position}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, alt, locale}')
            ->leftJoin('c.posts', 'p', 'WITH', 'p.active = :active')
            ->addSelect('PARTIAL p.{id}')
            ->where('c.id IN (:ids)')
            ->setParameter('active', true)
            ->setParameter('ids', $ids)
            ->orderBy('c.updatedAt', 'DESC');

        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getResult();

        if (!\is_array($result)) {
            return [];
        }

        return array_values(array_filter($result, static fn ($row): bool => $row instanceof Category));
    }

    public function findOne(string $slug): ?Category
    {
        $qb = $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id, createdAt, updatedAt}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug, locale}')
            ->leftJoin('c.media', 'm')
            ->addSelect('PARTIAL m.{id, media}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, title, alt, locale}')
            ->leftJoin('c.posts', 'p', 'WITH', 'p.active = :active')
            ->addSelect('PARTIAL p.{id, createdAt, updatedAt}')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('PARTIAL pt.{id, title, slug, excerpt, locale}')
            ->leftJoin('p.media', 'pm')
            ->addSelect('PARTIAL pm.{id, media}')
            ->leftJoin('pm.translations', 'pmt')
            ->addSelect('PARTIAL pmt.{id, title, alt, locale}')
            ->leftJoin('p.tags', 't')
            ->addSelect('PARTIAL t.{id}')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('PARTIAL tt.{id, title, locale}')
            ->where('ct.slug = :slug')
            ->setParameter('slug', $slug)
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC');

        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getOneOrNullResult();

        return $result instanceof Category ? $result : null;
    }
}
