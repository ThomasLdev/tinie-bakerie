<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * @return array<array-key,mixed>
     */
    #[\Override]
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('PARTIAL t.{id, isFeatured, createdAt, updatedAt}')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('PARTIAL tt.{id, title}')
            ->orderBy('t.createdAt', 'DESC');

        $result = $qb->getQuery()->getResult();

        return \is_array($result) ? $result : [];
    }

    public function findOne(int $id): ?Tag
    {
        $qb = $this->createQueryBuilder('t')
            ->select('PARTIAL t.{id, isFeatured, createdAt, updatedAt}')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('PARTIAL tt.{id, title}')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result instanceof Tag ? $result : null;
    }

    /**
     * @return list<Tag>
     */
    public function findFeatured(int $limit = 5): array
    {
        $idsResult = $this->createQueryBuilder('t')
            ->select('t.id')
            ->where('t.isFeatured = :featured')
            ->setParameter('featured', true)
            ->orderBy('t.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        $ids = array_column($idsResult, 'id');

        if ([] === $ids) {
            return [];
        }

        $qb = $this->createQueryBuilder('t')
            ->select('PARTIAL t.{id, image, isFeatured, createdAt, updatedAt}')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('PARTIAL tt.{id, title, locale}')
            ->leftJoin('t.posts', 'p')
            ->addSelect('PARTIAL p.{id}')
            ->where('t.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('t.updatedAt', 'DESC');

        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getResult();

        return \is_array($result) ? array_values($result) : [];
    }
}
