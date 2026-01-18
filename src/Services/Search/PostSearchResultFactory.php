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
     *     rank: float|string,
     *     headline: string|null
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
            mediaPath: $this->normalizeMediaPath($row['image_path']),
            rank: (float) $row['rank'],
            headline: $this->cleanHeadline($row['headline']),
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
     *     rank: float|string,
     *     headline: string|null
     * }> $rows
     *
     * @return PostSearchResult[]
     */
    public function createFromRows(array $rows): array
    {
        return array_map($this->createFromRow(...), $rows);
    }

    /**
     * Normalize the media path from database storage format.
     */
    private function normalizeMediaPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return $path;
    }

    /**
     * Clean the ts_headline output for display.
     * Converts <b> tags and strips any unwanted HTML.
     */
    private function cleanHeadline(?string $headline): ?string
    {
        if ($headline === null || $headline === '') {
            return null;
        }

        // ts_headline uses StartSel/StopSel markers, we configured <b></b>
        // Keep only the <b> tags, strip everything else for safety
        $allowed = '<b>';

        return strip_tags($headline, $allowed);
    }
}
