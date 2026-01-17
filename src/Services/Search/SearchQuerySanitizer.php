<?php

declare(strict_types=1);

namespace App\Services\Search;

final readonly class SearchQuerySanitizer
{
    /**
     * Sanitizes a search query for safe use with PostgreSQL Full-Text Search.
     *
     * - Trims whitespace
     * - Removes special characters that could cause FTS parsing errors
     * - Converts to lowercase for consistent matching
     * - Limits length to prevent abuse
     */
    public function sanitize(string $query, int $maxLength = 100): string
    {
        // Trim and limit length
        $query = mb_substr(trim($query), 0, $maxLength);

        // Remove characters that have special meaning in PostgreSQL FTS
        // Keep alphanumeric, spaces, and common accented characters
        $query = preg_replace('/[&|!():*<>\'"\[\]{}\\\\@#$%^]+/', ' ', $query) ?? $query;

        // Collapse multiple spaces into one
        $query = preg_replace('/\s+/', ' ', $query) ?? $query;

        return trim(mb_strtolower($query));
    }

    /**
     * Converts a sanitized query to a tsquery-compatible format.
     * Uses prefix matching (:*) for partial word matches.
     */
    public function toTsQuery(string $query): string
    {
        $sanitized = $this->sanitize($query);

        if ($sanitized === '') {
            return '';
        }

        // Split into words and add prefix matching
        $words = array_filter(explode(' ', $sanitized));

        if ($words === []) {
            return '';
        }

        // Join with & (AND) operator for multiple words
        // Add :* for prefix matching (allows partial word matches)
        return implode(' & ', array_map(
            static fn (string $word): string => $word . ':*',
            $words,
        ));
    }
}
