<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Repository\PostSearchRepositoryInterface;

/**
 * Orchestrates post search operations.
 * Delegates data access to repository and DTO creation to factory.
 */
final readonly class PostSearch
{
    public function __construct(
        private PostSearchRepositoryInterface $repository,
        private PostSearchResultFactory $resultFactory,
        private SearchQuerySanitizer $sanitizer,
    ) {
    }

    /**
     * Search posts by query string.
     *
     * @return PostSearchResult[]
     */
    public function search(string $query, int $limit = 5): array
    {
        $tsQuery = $this->sanitizer->toTsQuery($query);

        if ($tsQuery === '') {
            return [];
        }

        $rows = $this->repository->search($tsQuery, $limit);

        return $this->resultFactory->createFromRows($rows);
    }
}
