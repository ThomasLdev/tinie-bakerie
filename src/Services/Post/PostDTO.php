<?php

namespace App\Services\Post;

use App\Entity\CategoryTranslation;
use Doctrine\Common\Collections\Collection;

class PostDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public ?CategoryTranslation $category,
        public string $imageName,
        public Collection $tags,
        public Collection $sections,
        public \DateTime $createdAt,
        public \DateTime $updatedAt,
    ) {}
}
