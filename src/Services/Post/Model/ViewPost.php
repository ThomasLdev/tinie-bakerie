<?php

namespace App\Services\Post\Model;

use App\Entity\PostMedia;
use App\Entity\PostSection;
use App\Entity\PostTag;
use App\Services\EntityModelInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ViewPost implements EntityModelInterface
{
    /**
     * @param Collection<int, PostMedia>   $media
     * @param Collection<int, PostTag>     $tags
     * @param Collection<int, PostSection> $sections
     */
    public function __construct(
        public string $title = '',
        public string $slug = '',
        public string $categoryName = '',
        public string $categorySlug = '',
        public Collection $media = new ArrayCollection(),
        public Collection $tags = new ArrayCollection(),
        public Collection $sections = new ArrayCollection(),
        public \DateTime $createdAt = new \DateTime(),
        public \DateTime $updatedAt = new \DateTime(),
    ) {
    }
}
