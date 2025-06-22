<?php

namespace App\Services\Post\Model;

use DateTime;
use Doctrine\Common\Collections\Collection;

class ViewPost
{
    public function __construct(
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
