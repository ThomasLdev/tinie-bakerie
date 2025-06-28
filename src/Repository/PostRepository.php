<?php

namespace App\Repository;

use App\Entity\Post;
use App\Services\Post\Model\ViewPost;
use App\Services\Post\Model\ViewPostFactory;
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

    public function findAllByLocale(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, pt.title as postTitle, pt.slug AS postSlug, c.id AS categoryId, ct.slug AS categorySlug')
            ->join('p.translations', 'pt')
            ->leftJoin('p.category', 'c')
            ->leftJoin('c.translations', 'ct')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneBySlugAndLocale(string $slug): ?ViewPost
    {
        $data = $this->createQueryBuilder('p')
            ->addSelect('pt', 'ps', 't', 'tt', 'c', 'ct', 'pm')
            ->innerJoin('p.translations', 'pt')
            ->leftJoin('p.sections', 'ps')
            ->leftJoin('p.media', 'pm')
            ->leftJoin('p.tags', 't')
            ->leftJoin('t.translations', 'tt')
            ->leftJoin('p.category', 'c')
            ->leftJoin('c.translations', 'ct')
            ->where('pt.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        return ViewPostFactory::create($data);
    }
}
