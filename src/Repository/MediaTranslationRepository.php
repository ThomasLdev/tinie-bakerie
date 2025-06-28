<?php

namespace App\Repository;

use App\Entity\PostSectionMediaTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostSectionMediaTranslation>
 */
class MediaTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostSectionMediaTranslation::class);
    }
}
