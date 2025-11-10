<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
            ->select('PARTIAL t.{id, backgroundColor, textColor, createdAt, updatedAt}')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('PARTIAL tt.{id, title}')
            ->orderBy('t.createdAt', 'DESC');

        $result = $qb->getQuery()->getResult();

        return \is_array($result) ? $result : [];
    }

    public function findOne(int $id): ?Tag
    {
        $qb = $this->createQueryBuilder('t')
            ->select('PARTIAL t.{id, backgroundColor, textColor, createdAt, updatedAt}')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('PARTIAL tt.{id, title}')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result instanceof Tag ? $result : null;
    }
}
