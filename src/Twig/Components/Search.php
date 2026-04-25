<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Services\Search\PostSearch;
use App\Services\Search\PostSearchResult;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Search
{
    use DefaultActionTrait;

    private const int MIN_QUERY_LENGTH = 2;
    private const int RESULT_LIMIT = 24;

    /**
     * @var list<string>
     */
    private const array SUGGESTIONS = [
        'Tarte tatin',
        'Chocolat',
        'Citron',
        'Vanille',
        'Rhubarbe',
        'Caramel',
        'Sans gluten',
    ];

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
        if (mb_strlen($this->query) < self::MIN_QUERY_LENGTH) {
            return [];
        }

        return $this->postSearch->search($this->query, limit: self::RESULT_LIMIT);
    }

    public function isEmpty(): bool
    {
        return mb_strlen($this->query) < self::MIN_QUERY_LENGTH;
    }

    /**
     * @return list<string>
     */
    public function getSuggestions(): array
    {
        return self::SUGGESTIONS;
    }

    #[LiveAction]
    public function clear(): void
    {
        $this->query = '';
    }

    #[LiveAction]
    public function fillQuery(#[LiveArg] string $value): void
    {
        $this->query = $value;
    }
}
