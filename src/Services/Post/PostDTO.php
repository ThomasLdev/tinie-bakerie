<?php

namespace App\Services\Post;

use DateTime;
use Doctrine\Common\Collections\Collection;

class PostDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public string $categoryName,
        public string $categorySlug,
        public Collection $media,
        public Collection $tags,
        public Collection $sections,
        public DateTime $createdAt,
        public DateTime $updatedAt,
    ) {}
}
