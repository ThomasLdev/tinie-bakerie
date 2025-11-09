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

    /**
     * @return array<array-key,mixed>
     */
    #[\Override]
    public function findAll(): array
    {
        // Clear the identity map to ensure fresh entities are loaded with the locale filter applied
        $this->getEntityManager()->clear();
        
        $qb = $this->createQueryBuilder('c')
            ->select('PARTIAL c.{id, createdAt, updatedAt}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug}')
            ->leftJoin('c.media', 'm')
            ->addSelect('PARTIAL m.{id, mediaName, type}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, title, alt}')
            ->orderBy('c.createdAt', 'DESC');

        $result = $qb->getQuery()->getResult();

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

        $result = $qb->getQuery()->getResult();

        return \is_array($result) ? $result : [];
    }

    public function findOne(string $slug): ?Category
    {
        // Clear the identity map to ensure fresh entities are loaded with the locale filter applied
        $this->getEntityManager()->clear();
        
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
