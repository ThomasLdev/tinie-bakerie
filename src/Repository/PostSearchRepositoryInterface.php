<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * Contract for post search operations.
 * Allows swapping implementations (PostgreSQL FTS, Elasticsearch, etc.).
 */
interface PostSearchRepositoryInterface
{
    /**
     * Search posts using full-text search.
     *
     * @return list<array{
     *     id: int,
     *     title: string,
     *     slug: string,
     *     excerpt: string,
     *     category_title: string|null,
     *     category_slug: string|null,
     *     image_path: string|null,
     *     rank: float
     * }>
     */
    public function search(string $tsQuery, string $locale, int $limit): array;
}
