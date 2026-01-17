<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Services\Locale\LocaleProvider;
use App\Services\Search\PostSearchResult;
use App\Services\Search\PostSearchService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Search
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private readonly PostSearchService $searchService,
        private readonly LocaleProvider $localeProvider,
    ) {
    }

    /**
     * @return PostSearchResult[]
     */
    public function getResults(): array
    {
        if (mb_strlen($this->query) < 2) {
            return [];
        }

        return $this->searchService->search($this->query, $this->localeProvider->getCurrentLocale(), limit: 15);
    }
}
