<?php

namespace App\Repository;

use App\Entity\Post;
use App\Services\Post\PostDTO;
use App\Services\Post\PostDTOFactory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
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

    public function findAllByLocale(string $locale): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.imageName, pt.title as postTitle, pt.slug AS postSlug, c.id AS categoryId, ct.slug AS categorySlug')
            ->join('p.translations', 'pt', 'WITH', 'pt.locale = :locale')
            ->leftJoin('p.category', 'c')
            ->leftJoin('c.translations', 'ct', 'WITH', 'ct.locale = :locale')
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneBySlugAndLocale(string $slug, string $locale): ?PostDTO
    {
        $data = $this->createQueryBuilder('p')
            ->addSelect('pt', 'ps', 't', 'tt', 'c', 'ct', 'pm')
            ->innerJoin('p.translations', 'pt', 'WITH', 'pt.locale = :locale')
            ->leftJoin('p.sections', 'ps')
            ->leftJoin('p.media', 'pm')
            ->leftJoin('p.tags', 't')
            ->leftJoin('t.translations', 'tt', 'WITH', 'tt.locale = :locale')
            ->leftJoin('p.category', 'c')
            ->leftJoin('c.translations', 'ct', 'WITH', 'ct.locale = :locale')
            ->where('pt.slug = :slug')
            ->setParameter('slug', $slug)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getOneOrNullResult();

        return PostDTOFactory::create($data);
    }
}
