<?php

declare(strict_types=1);

namespace App\Services\Search;

/**
 * Factory for creating PostSearchResult DTOs from raw database rows.
 */
final readonly class PostSearchResultFactory
{
    /**
     * @param array{
     *     id: int|string,
     *     title: string,
     *     slug: string,
     *     excerpt: string,
     *     category_title: string|null,
     *     category_slug: string|null,
     *     image_path: string|null,
     *     rank: float|string
     * } $row
     */
    public function createFromRow(array $row): PostSearchResult
    {
        return new PostSearchResult(
            id: (int) $row['id'],
            title: $row['title'],
            slug: $row['slug'],
            excerpt: $row['excerpt'],
            categoryTitle: $row['category_title'] ?? '',
            categorySlug: $row['category_slug'] ?? '',
            imagePath: $this->normalizeImagePath($row['image_path']),
            rank: (float) $row['rank'],
        );
    }

    /**
     * @param array<int, array{
     *     id: int|string,
     *     title: string,
     *     slug: string,
     *     excerpt: string,
     *     category_title: string|null,
     *     category_slug: string|null,
     *     image_path: string|null,
     *     rank: float|string
     * }> $rows
     *
     * @return PostSearchResult[]
     */
    public function createFromRows(array $rows): array
    {
        return array_map($this->createFromRow(...), $rows);
    }

    /**
     * Normalize the image path from database storage format.
     * JoliCode MediaBundle stores paths that may need cleanup.
     */
    private function normalizeImagePath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return $path;
    }
}
