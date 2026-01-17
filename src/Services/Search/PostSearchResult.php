<?php

declare(strict_types=1);

namespace App\Services\Search;

/**
 * Lightweight DTO containing all data needed to display a search result.
 * No Doctrine entity hydration required.
 */
final readonly class PostSearchResult
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public string $excerpt,
        public string $categoryTitle,
        public string $categorySlug,
        public ?string $imagePath,
        public float $rank,
        public ?string $headline,
    ) {
    }

    /**
     * Returns the headline if available, otherwise truncates the excerpt.
     */
    public function getDisplaySnippet(int $maxLength = 150): string
    {
        if ($this->headline !== null && $this->headline !== '') {
            return $this->headline;
        }

        if (mb_strlen($this->excerpt) <= $maxLength) {
            return $this->excerpt;
        }

        return mb_substr($this->excerpt, 0, $maxLength) . '...';
    }
}
