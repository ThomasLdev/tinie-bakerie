<?php

declare(strict_types=1);

namespace App\Services\Search;

use JoliCode\MediaBundle\Exception\MediaNotFoundException;
use JoliCode\MediaBundle\Model\Media;
use JoliCode\MediaBundle\Resolver\Resolver;

final readonly class PostSearchResultFactory
{
    public function __construct(
        private Resolver $resolver,
    ) {
    }

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
            media: $this->resolveMedia($row['image_path']),
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

    private function resolveMedia(?string $path): ?Media
    {
        if ($path === null || $path === '') {
            return null;
        }

        try {
            return $this->resolver->resolveMedia($path);
        } catch (MediaNotFoundException) {
            return null;
        }
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
        $allowed = '<b>';

        return strip_tags($headline, $allowed);
    }
}
