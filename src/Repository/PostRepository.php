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
        // Clear the identity map to ensure fresh entities are loaded with the locale filter applied
        $this->getEntityManager()->clear();

        $qb = $this->createQueryBuilder('p')
            ->select('PARTIAL p.{id, createdAt, updatedAt}')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('PARTIAL pt.{id, title, slug}')
            ->leftJoin('p.category', 'c')
            ->addSelect('PARTIAL c.{id}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug}')
            ->leftJoin('p.tags', 't')
            ->addSelect('PARTIAL t.{id, backgroundColor, textColor}')
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
        // Clear the identity map to ensure fresh entities are loaded with the locale filter applied
        $this->getEntityManager()->clear();

        $qb = $this->createQueryBuilder('p')
            ->select('PARTIAL p.{id, createdAt, updatedAt}')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('PARTIAL pt.{id, title, slug}')
            ->leftJoin('p.category', 'c')
            ->addSelect('PARTIAL c.{id}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug}')
            ->leftJoin('p.tags', 't')
            ->addSelect('PARTIAL t.{id, backgroundColor, textColor}')
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
}
