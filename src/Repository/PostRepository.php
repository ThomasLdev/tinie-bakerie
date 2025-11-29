<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return array<array-key,mixed>
     */
    public function findAllActive(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('PARTIAL p.{id, createdAt, updatedAt}')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('PARTIAL pt.{id, title, slug, locale}')
            ->leftJoin('p.category', 'c')
            ->addSelect('PARTIAL c.{id}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug, locale}')
            ->leftJoin('p.tags', 't')
            ->addSelect('PARTIAL t.{id, backgroundColor, textColor}')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('PARTIAL tt.{id, title, locale}')
            ->leftJoin('p.media', 'm')
            ->addSelect('PARTIAL m.{id, mediaName, type}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, title, alt, locale}')
            ->where('p.active = :active')
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC');

        // Use HINT_REFRESH to bypass identity map and ensure locale filter is applied
        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getResult();

        return \is_array($result) ? $result : [];
    }

    public function findOneActive(string $slug): ?Post
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('pt')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('ct')
            ->leftJoin('p.tags', 't')
            ->addSelect('t')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('tt')
            ->leftJoin('p.sections', 'ps')
            ->addSelect('ps')
            ->leftJoin('ps.translations', 'pst')
            ->addSelect('pst')
            ->leftJoin('ps.media', 'psm')
            ->addSelect('psm')
            ->leftJoin('psm.translations', 'psmt')
            ->addSelect('psmt')
            ->leftJoin('p.media', 'm')
            ->addSelect('m')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('mt')
            ->where('p.active = :active')
            ->andWhere('pt.slug = :slug')
            ->setParameter('active', true)
            ->setParameter('slug', $slug)
            ->orderBy('p.createdAt', 'DESC');

        // Use HINT_REFRESH to bypass identity map and ensure locale filter is applied
        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getOneOrNullResult();

        return $result instanceof Post ? $result : null;
    }

    /**
     * Find an active post by ID with all related data.
     * Uses HINT_REFRESH to ensure locale filter is applied.
     */
    public function findOneActiveById(int $id): ?Post
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('pt')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('ct')
            ->leftJoin('p.tags', 't')
            ->addSelect('t')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('tt')
            ->leftJoin('p.sections', 'ps')
            ->addSelect('ps')
            ->leftJoin('ps.translations', 'pst')
            ->addSelect('pst')
            ->leftJoin('ps.media', 'psm')
            ->addSelect('psm')
            ->leftJoin('psm.translations', 'psmt')
            ->addSelect('psmt')
            ->leftJoin('p.media', 'm')
            ->addSelect('m')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('mt')
            ->where('p.active = :active')
            ->andWhere('p.id = :id')
            ->setParameter('active', true)
            ->setParameter('id', $id)
            ->setMaxResults(1);

        // Use HINT_REFRESH to bypass identity map and ensure locale filter is applied
        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getOneOrNullResult();

        return $result instanceof Post ? $result : null;
    }
}
