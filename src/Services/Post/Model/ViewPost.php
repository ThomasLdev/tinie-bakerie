<?php

namespace App\Services\Post\Model;

use App\Services\EntityModelInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ViewPost implements EntityModelInterface
{
    public function __construct(
        public string $title = '',
        public string $slug = '',
        public string $categoryName = '',
        public string $categorySlug = '',
        public Collection $media = new ArrayCollection(),
        public Collection $tags = new ArrayCollection(),
        public Collection $sections = new ArrayCollection(),
        public DateTime $createdAt = new DateTime(),
        public DateTime $updatedAt = new DateTime(),
    ) {}
}
