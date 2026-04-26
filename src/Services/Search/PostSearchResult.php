<?php

declare(strict_types=1);

namespace App\Services\Search;

use JoliCode\MediaBundle\Model\Media;

final readonly class PostSearchResult
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public string $excerpt,
        public string $categoryTitle,
        public string $categorySlug,
        public ?Media $media,
        public float $rank,
        public ?string $headline,
    ) {
    }
}
