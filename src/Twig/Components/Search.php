<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Services\Search\PostSearch;
use App\Services\Search\PostSearchResult;
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
        private readonly PostSearch $postSearch,
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

        return $this->postSearch->search($this->query, limit: 15);
    }
}
