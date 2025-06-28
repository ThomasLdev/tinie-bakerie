<?php

namespace App\Repository;

use App\Entity\CategoryMediaTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryMediaTranslation>
 */
class CategoryMediaTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryMediaTranslation::class);
    }
}
