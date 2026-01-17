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
     * Locale is determined internally by the implementation.
     *
     * @return list<array{
     *     id: int,
     *     title: string,
     *     slug: string,
     *     excerpt: string,
     *     category_title: string|null,
     *     category_slug: string|null,
     *     image_path: string|null,
     *     rank: float,
     *     headline: string|null
     * }>
     */
    public function search(string $tsQuery, int $limit): array;
}
