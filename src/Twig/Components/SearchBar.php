<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Meilisearch\Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class SearchBar
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    private int $estimatedTotalHits = 0;

    private int $cachedHitsCount = 0;

    public function __construct(
        private readonly Client $client,
        private readonly RequestStack $requestStack,
        #[Autowire(param: 'meilisearch.prefix')]
        private readonly string $prefix,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getResults(): array
    {
        if (\strlen($this->query) < 2) {
            $this->estimatedTotalHits = 0;
            $this->cachedHitsCount = 0;

            return [];
        }

        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'fr';
        $indexName = \sprintf('%sposts_%s', $this->prefix, $locale);

        try {
            $results = $this->client->index($indexName)->search(
                $this->query,
                [
                    'limit' => 5,
                    'attributesToHighlight' => ['title', 'excerpt'],
                    'filter' => 'isActive = true',
                    'locales' => [$locale],
                ],
            );

            $hits = $results->getHits();

            $this->cachedHitsCount = \count($hits);
            $this->estimatedTotalHits = $results->getEstimatedTotalHits() ?? 0;

            return $hits;
        } catch (\Exception) {
            $this->estimatedTotalHits = 0;
            $this->cachedHitsCount = 0;

            return [];
        }
    }

    public function hasResults(): bool
    {
        return \strlen($this->query) >= 2 && $this->cachedHitsCount > 0;
    }

    public function getResultCount(): int
    {
        return $this->estimatedTotalHits;
    }
}
