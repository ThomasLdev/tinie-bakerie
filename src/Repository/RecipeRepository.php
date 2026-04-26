<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    public function findLatestActive(): ?Recipe
    {
        $qb = $this->createQueryBuilder('r')
            ->select('PARTIAL r.{id, createdAt, cookingTime, preparationTime, difficulty, servings}')
            ->leftJoin('r.translations', 'rt')
            ->addSelect('PARTIAL rt.{id, title, slug, excerpt, locale}')
            ->leftJoin('r.category', 'c')
            ->addSelect('PARTIAL c.{id}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, title, slug, locale}')
            ->leftJoin('r.tags', 't')
            ->addSelect('PARTIAL t.{id}')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('PARTIAL tt.{id, title, locale}')
            ->leftJoin('r.media', 'm')
            ->addSelect('PARTIAL m.{id, media, position}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, alt, locale}')
            ->where('r.active = :active')
            ->setParameter('active', true)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getOneOrNullResult();

        return $result instanceof Recipe ? $result : null;
    }

    /**
     * @return list<Recipe>
     */
    public function findFeatured(int $limit = 5): array
    {
        $idsResult = $this->createQueryBuilder('r')
            ->select('r.id')
            ->where('r.active = :active')
            ->andWhere('r.isFeatured = :featured')
            ->setParameter('active', true)
            ->setParameter('featured', true)
            ->orderBy('r.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        $ids = array_column($idsResult, 'id');

        if ([] === $ids) {
            return [];
        }

        $qb = $this->createQueryBuilder('r')
            ->select('PARTIAL r.{id, createdAt, updatedAt, cookingTime, preparationTime, difficulty, servings}')
            ->leftJoin('r.translations', 'rt')
            ->addSelect('PARTIAL rt.{id, title, slug, excerpt, locale}')
            ->leftJoin('r.category', 'c')
            ->addSelect('PARTIAL c.{id}')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('PARTIAL ct.{id, slug, locale}')
            ->leftJoin('r.media', 'm')
            ->addSelect('PARTIAL m.{id, media, position}')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('PARTIAL mt.{id, alt, locale}')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('r.updatedAt', 'DESC');

        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getResult();

        if (!\is_array($result)) {
            return [];
        }

        return array_values(array_filter($result, static fn ($row): bool => $row instanceof Recipe));
    }

    public function findOneActive(string $slug): ?Recipe
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r')
            ->leftJoin('r.translations', 'rt')
            ->addSelect('rt')
            ->leftJoin('r.category', 'c')
            ->addSelect('c')
            ->leftJoin('c.translations', 'ct')
            ->addSelect('ct')
            ->leftJoin('r.tags', 't')
            ->addSelect('t')
            ->leftJoin('t.translations', 'tt')
            ->addSelect('tt')
            ->leftJoin('r.sections', 'rs')
            ->addSelect('rs')
            ->leftJoin('rs.translations', 'rst')
            ->addSelect('rst')
            ->leftJoin('rs.media', 'rsm')
            ->addSelect('rsm')
            ->leftJoin('rsm.translations', 'rsmt')
            ->addSelect('rsmt')
            ->leftJoin('r.ingredientGroups', 'ig')
            ->addSelect('ig')
            ->leftJoin('ig.translations', 'igt')
            ->addSelect('igt')
            ->leftJoin('ig.ingredients', 'i')
            ->addSelect('i')
            ->leftJoin('i.translations', 'it')
            ->addSelect('it')
            ->leftJoin('r.media', 'm')
            ->addSelect('m')
            ->leftJoin('m.translations', 'mt')
            ->addSelect('mt')
            ->where('r.active = :active')
            ->andWhere('rt.slug = :slug')
            ->setParameter('active', true)
            ->setParameter('slug', $slug)
            ->orderBy('r.createdAt', 'DESC');

        $result = $qb->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getOneOrNullResult();

        return $result instanceof Recipe ? $result : null;
    }
}
