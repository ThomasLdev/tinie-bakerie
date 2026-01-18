<?php

declare(strict_types=1);

namespace App\Services\Search;

final readonly class PostSearchResult
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public string $excerpt,
        public string $categoryTitle,
        public string $categorySlug,
        public ?string $mediaPath,
        public float $rank,
        public ?string $headline,
    ) {
    }
}
