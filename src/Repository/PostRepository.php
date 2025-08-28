<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;
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
            ->select('PARTIAL p.{id, title, active, createdAt, updatedAt, slug}')
            ->addSelect('PARTIAL c.{id, title, slug}')
            ->addSelect('PARTIAL t.{id, title, color}')
            ->addSelect('PARTIAL pm.{id, mediaName, alt, type, title}')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.tags', 't')
            ->leftJoin('p.media', 'pm')
            ->where('p.active = :active ')
            ->setParameter('active', true)
            ->orderBy('p.title', 'ASC');

        $query = $qb->getQuery();
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class);
        $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        $result = $query->getResult();

        return is_array($result) ? $result : [];
    }

    public function findOnePublishedBySlug(string $slug): ?Post
    {
        $qb = $this->createQueryBuilder('p')
            ->select('PARTIAL p.{id, title, active, createdAt, updatedAt, slug}')
            ->addSelect('PARTIAL c.{id, title, slug}')
            ->addSelect('PARTIAL t.{id, title, color}')
            ->addSelect('PARTIAL pm.{id, mediaName, alt, type, title}')
            ->addSelect('PARTIAL ps.{id, position, content, type}')
            ->addSelect('PARTIAL psm.{id, mediaName, alt, type, title}')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.tags', 't')
            ->leftJoin('p.media', 'pm')
            ->leftJoin('p.sections', 'ps')
            ->leftJoin('ps.media', 'psm')
            ->where('p.slug = :slug')
            ->andWhere('p.active = :active ')
            ->setParameters(new ArrayCollection([
                new Parameter('slug', $slug),
                new Parameter('active',true)
            ]));

        $query = $qb->getQuery();
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class);
        $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        $result = $query->getOneOrNullResult();

        return $result instanceof Post ? $result : null;
    }

    public function findRandomPublished(): ?Post
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.active = :active ')
            ->setParameter('active', true)
            ->setMaxResults(1)
        ;

        $query = $qb->getQuery();
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class);
        $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        $result = $query->getOneOrNullResult();

        return $result instanceof Post ? $result : null;
    }
}
