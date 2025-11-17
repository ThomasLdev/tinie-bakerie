<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Bundle\SearchService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class SearchBar
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp]
    public bool $showResults = false;

    public function __construct(
        private readonly SearchService $searchService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getResults(): array
    {
        if (strlen($this->query) < 2) {
            return [];
        }

        try {
            $results = $this->searchService->rawSearch(
                Post::class,
                $this->query,
                [
                    'limit' => 5,
                    'attributesToHighlight' => ['title', 'excerpt'],
                    'filter' => 'isActive = true',
                ]
            );

            return $results['hits'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function hasResults(): bool
    {
        return strlen($this->query) >= 2 && count($this->getResults()) > 0;
    }

    public function getResultCount(): int
    {
        if (strlen($this->query) < 2) {
            return 0;
        }

        try {
            $results = $this->searchService->rawSearch(
                Post::class,
                $this->query,
                [
                    'limit' => 0,
                    'filter' => 'isActive = true',
                ]
            );

            return $results['estimatedTotalHits'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
