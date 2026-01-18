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
            ->addSelect('PARTIAL t.{id, backgroundColor, textColor}')
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
