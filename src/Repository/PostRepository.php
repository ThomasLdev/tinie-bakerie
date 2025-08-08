<?php

namespace App\Repository;

use App\Entity\Post;
use App\Services\Post\ListPostModel;
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
     * @return array<array-key, ListPostModel>
     */
    public function forListing(): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('NEW App\Services\Post\ListPostModel(p.title, p.publishedAt, p.createdAt, p.updatedAt)')
            ->getQuery()
            ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class)
        ;

        return $query->getResult();
    }
}
