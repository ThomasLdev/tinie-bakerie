<?php

namespace App\Repository;

use App\Entity\Post;
use App\Services\Post\Model\ViewPost;
use App\Services\Post\Model\ViewPostFactory;
use App\Services\Post\Model\ViewPostList;
use App\Services\Post\Model\ViewPostListCollectionFactory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(
        private readonly ViewPostFactory $factory,
        private readonly ViewPostListCollectionFactory $listFactory,
        ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }
    /**
     * @return ArrayCollection<array-key, ViewPostList>
     */
    public function findAllByLocale(): ArrayCollection
    {
        $data = $this->createQueryBuilder('p')
            ->select('p.id, pt.title as postTitle, pt.slug AS postSlug, c.id AS categoryId, ct.slug AS categorySlug')
            ->join('p.translations', 'pt')
            ->leftJoin('p.category', 'c')
            ->leftJoin('c.translations', 'ct')
            ->getQuery()
            ->getResult()
        ;

        if (!is_array($data) || [] === $data) {
            return new ArrayCollection();
        }

        return $this->listFactory->create($data);
    }

    public function findOneBySlugAndLocale(string $slug): ViewPost
    {
        $post = $this->createQueryBuilder('p')
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

        if (!$post instanceof Post) {
            throw new NotFoundHttpException();
        }

        return $this->factory->create($post);
    }
}
