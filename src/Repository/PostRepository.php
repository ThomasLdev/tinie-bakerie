<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
            ->addSelect('PARTIAL pt.{id, title, slug}')
            ->leftJoin('p.category', 'c')
            ->addSelect('PARTIAL c.{id}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug}')
            ->leftJoin('p.tags', 't')
            ->addSelect('PARTIAL t.{id, color}')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('PARTIAL tt.{id, title}')
            ->leftJoin('p.media', 'm')
            ->addSelect('PARTIAL m.{id, mediaName, type}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, title, alt}')
            ->where('p.active = :active')
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC');

        $result = $qb->getQuery()->getResult();

        return \is_array($result) ? $result : [];
    }

    public function findOneActive(string $slug): ?Post
    {
        $qb = $this->createQueryBuilder('p')
            ->select('PARTIAL p.{id, createdAt, updatedAt}')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('PARTIAL pt.{id, title, slug}')
            ->leftJoin('p.category', 'c')
            ->addSelect('PARTIAL c.{id}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug}')
            ->leftJoin('p.tags', 't')
            ->addSelect('PARTIAL t.{id, color}')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('PARTIAL tt.{id, title}')
            ->leftJoin('p.sections', 'ps')
            ->addSelect('PARTIAL ps.{id, position, type}')
            ->leftJoin('ps.translations', 'pst')
            ->addSelect('PARTIAL pst.{id, content}')
            ->leftJoin('ps.media', 'psm')
            ->addSelect('PARTIAL psm.{id, mediaName, type}')
            ->leftJoin('psm.translations', 'psmt')
            ->addSelect('PARTIAL psmt.{id, alt, title}')
            ->leftJoin('p.media', 'm')
            ->addSelect('PARTIAL m.{id, mediaName, type}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, title, alt}')
            ->where('p.active = :active')
            ->andWhere('pt.slug = :slug')
            ->setParameter('active', true)
            ->setParameter('slug', $slug)
            ->orderBy('p.createdAt', 'DESC');

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result instanceof Post ? $result : null;
    }

    /**
     * Search for posts using PostgreSQL full-text search.
     * Searches in post titles, excerpts, content sections, categories, and tags.
     *
     * @param string $query Search query
     * @param string|null $locale Locale for search (null for all locales)
     * @return array<array-key,mixed>
     */
    public function searchPosts(string $query, ?string $locale = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('PARTIAL p.{id, createdAt, updatedAt}')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('PARTIAL pt.{id, title, slug, excerpt}')
            ->leftJoin('p.category', 'c')
            ->addSelect('PARTIAL c.{id}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug}')
            ->leftJoin('p.tags', 't')
            ->addSelect('PARTIAL t.{id, color}')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('PARTIAL tt.{id, title}')
            ->leftJoin('p.sections', 'ps')
            ->leftJoin('ps.translations', 'pst')
            ->leftJoin('p.media', 'm')
            ->addSelect('PARTIAL m.{id, mediaName, type}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, title, alt}')
            ->where('p.active = :active');

        // Build the search conditions using PostgreSQL's to_tsvector and plainto_tsquery
        $searchConditions = [
            "to_tsvector('simple', COALESCE(pt.title, '')) @@ plainto_tsquery('simple', :query)",
            "to_tsvector('simple', COALESCE(pt.excerpt, '')) @@ plainto_tsquery('simple', :query)",
            "to_tsvector('simple', COALESCE(pst.content, '')) @@ plainto_tsquery('simple', :query)",
            "to_tsvector('simple', COALESCE(ct.title, '')) @@ plainto_tsquery('simple', :query)",
            "to_tsvector('simple', COALESCE(tt.title, '')) @@ plainto_tsquery('simple', :query)"
        ];

        $qb->andWhere('(' . implode(' OR ', $searchConditions) . ')');

        // Add locale filter if specified
        if ($locale !== null) {
            $qb->andWhere('(pt.locale = :locale OR pt.locale IS NULL)')
               ->setParameter('locale', $locale);
        }

        $qb->setParameter('active', true)
           ->setParameter('query', $query)
           ->orderBy('p.createdAt', 'DESC');

        $result = $qb->getQuery()->getResult();

        return \is_array($result) ? $result : [];
    }

    /**
     * Search for posts with ranking using PostgreSQL full-text search.
     * Returns results ordered by relevance score.
     *
     * @param string $query Search query
     * @param string|null $locale Locale for search (null for all locales)
     * @return array<array-key,mixed>
     */
    public function searchPostsWithRanking(string $query, ?string $locale = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('PARTIAL p.{id, createdAt, updatedAt}')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('PARTIAL pt.{id, title, slug, excerpt}')
            ->leftJoin('p.category', 'c')
            ->addSelect('PARTIAL c.{id}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug}')
            ->leftJoin('p.tags', 't')
            ->addSelect('PARTIAL t.{id, color}')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('PARTIAL tt.{id, title}')
            ->leftJoin('p.sections', 'ps')
            ->leftJoin('ps.translations', 'pst')
            ->leftJoin('p.media', 'm')
            ->addSelect('PARTIAL m.{id, mediaName, type}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, title, alt}')
            ->where('p.active = :active');

        // Calculate relevance score with different weights for different fields
        $rankingFormula = "
            GREATEST(
                ts_rank(to_tsvector('simple', COALESCE(pt.title, '')), plainto_tsquery('simple', :query)) * 3,
                ts_rank(to_tsvector('simple', COALESCE(pt.excerpt, '')), plainto_tsquery('simple', :query)) * 2,
                ts_rank(to_tsvector('simple', COALESCE(pst.content, '')), plainto_tsquery('simple', :query)),
                ts_rank(to_tsvector('simple', COALESCE(ct.title, '')), plainto_tsquery('simple', :query)) * 1.5,
                ts_rank(to_tsvector('simple', COALESCE(tt.title, '')), plainto_tsquery('simple', :query)) * 1.5
            )
        ";

        $qb->addSelect("({$rankingFormula}) AS HIDDEN relevance_score");

        // Search conditions
        $searchConditions = [
            "to_tsvector('simple', COALESCE(pt.title, '')) @@ plainto_tsquery('simple', :query)",
            "to_tsvector('simple', COALESCE(pt.excerpt, '')) @@ plainto_tsquery('simple', :query)",
            "to_tsvector('simple', COALESCE(pst.content, '')) @@ plainto_tsquery('simple', :query)",
            "to_tsvector('simple', COALESCE(ct.title, '')) @@ plainto_tsquery('simple', :query)",
            "to_tsvector('simple', COALESCE(tt.title, '')) @@ plainto_tsquery('simple', :query)"
        ];

        $qb->andWhere('(' . implode(' OR ', $searchConditions) . ')');

        // Add locale filter if specified
        if ($locale !== null) {
            $qb->andWhere('(pt.locale = :locale OR pt.locale IS NULL)')
               ->setParameter('locale', $locale);
        }

        $qb->setParameter('active', true)
           ->setParameter('query', $query)
           ->orderBy('relevance_score', 'DESC')
           ->addOrderBy('p.createdAt', 'DESC');

        $result = $qb->getQuery()->getResult();

        return \is_array($result) ? $result : [];
    }
}
