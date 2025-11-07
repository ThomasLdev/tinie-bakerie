<?php
// Example fix for PostRepository::findOneActive()
// Copy relevant parts to src/Repository/PostRepository.php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Get the active locale from the enabled locale_filter.
     * Falls back to 'fr' if filter is not enabled.
     */
    private function getLocaleFromFilter(): string
    {
        $filters = $this->getEntityManager()->getFilters();
        
        if (!$filters->isEnabled('locale_filter')) {
            return 'fr'; // Default locale
        }
        
        /** @var \App\Services\Filter\LocaleFilter $filter */
        $filter = $filters->getFilter('locale_filter');
        
        // The parameter is stored with quotes, e.g., "'en'"
        $locale = $filter->getParameter('locale');
        
        return trim($locale, "'\""); // Remove quotes
    }

    public function findOneActive(string $slug): ?Post
    {
        $locale = $this->getLocaleFromFilter();
        
        $qb = $this->createQueryBuilder('p')
            ->select('PARTIAL p.{id, createdAt, updatedAt}')
            
            // Post translations - auto-filtered by locale_filter
            ->leftJoin('p.translations', 'pt')
            ->addSelect('PARTIAL pt.{id, title, slug}')
            
            // Category + translations - EXPLICIT locale filter
            ->leftJoin('p.category', 'c')
            ->addSelect('PARTIAL c.{id}')
            ->leftJoin('c.translations', 'ct', 'WITH', 'ct.locale = :locale')  // ← FIX
            ->addSelect('PARTIAL ct.{id, title, slug}')
            
            // Tags + translations - EXPLICIT locale filter
            ->leftJoin('p.tags', 't')
            ->addSelect('PARTIAL t.{id, backgroundColor, textColor}')
            ->leftJoin('t.translations', 'tt', 'WITH', 'tt.locale = :locale')  // ← FIX
            ->addSelect('PARTIAL tt.{id, title}')
            
            // Sections + translations - EXPLICIT locale filter
            ->leftJoin('p.sections', 'ps')
            ->addSelect('PARTIAL ps.{id, position, type}')
            ->leftJoin('ps.translations', 'pst', 'WITH', 'pst.locale = :locale')  // ← FIX
            ->addSelect('PARTIAL pst.{id, content}')
            
            // Section media + translations - EXPLICIT locale filter
            ->leftJoin('ps.media', 'psm')
            ->addSelect('PARTIAL psm.{id, mediaName, type}')
            ->leftJoin('psm.translations', 'psmt', 'WITH', 'psmt.locale = :locale')  // ← FIX
            ->addSelect('PARTIAL psmt.{id, alt, title}')
            
            // Post media + translations - EXPLICIT locale filter
            ->leftJoin('p.media', 'm')
            ->addSelect('PARTIAL m.{id, mediaName, type}')
            ->leftJoin('m.translations', 'mt', 'WITH', 'mt.locale = :locale')  // ← FIX
            ->addSelect('PARTIAL mt.{id, title, alt}')
            
            ->where('p.active = :active')
            ->andWhere('pt.slug = :slug')
            ->setParameter('active', true)
            ->setParameter('slug', $slug)
            ->setParameter('locale', $locale)  // ← ADD THIS
            ->orderBy('p.createdAt', 'DESC');

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result instanceof Post ? $result : null;
    }

    // Apply same pattern to findAllActive()
}
