<?php

declare(strict_types=1);

namespace App\Services\Search;

interface PostSearchInterface
{
    /**
     * Search posts by query string.
     *
     * @return PostSearchResult[]
     */
    public function search(string $query, int $limit = 5): array;
}
