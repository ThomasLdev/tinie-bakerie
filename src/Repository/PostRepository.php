<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;

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
    public function findAllPublished(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('PARTIAL p.{id, title, publishedAt, createdAt, updatedAt, slug}')
            ->addSelect('PARTIAL c.{id, title, slug}')
            ->leftJoin('p.category', 'c')
            ->where('p.publishedAt IS NOT NULL')
            ->orderBy('p.publishedAt', 'DESC');

        $query = $qb->getQuery();
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class);
        $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        $result = $query->getResult();

        return is_array($result) ? $result : [];
    }

    public function findOnePublishedBySlug(string $slug): ?Post
    {
        $qb = $this->createQueryBuilder('p')
            ->select('PARTIAL p.{id, title, publishedAt, createdAt, updatedAt, slug}')
            ->addSelect('PARTIAL c.{id, title, slug}')
            ->leftJoin('p.category', 'c')
            ->where('p.slug = :slug')
            ->andWhere('p.publishedAt IS NOT NULL')
            ->setParameter('slug', $slug);

        $query = $qb->getQuery();
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class);
        $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        $result = $query->getOneOrNullResult();

        return $result instanceof Post ? $result : null;
    }

    public function findRandomPublished(): ?Post
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.publishedAt IS NOT NULL')
            ->setMaxResults(1)
        ;

        $query = $qb->getQuery();
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class);
        $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        $result = $query->getOneOrNullResult();

        return $result instanceof Post ? $result : null;
    }
}
