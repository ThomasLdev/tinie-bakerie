<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig\Components;

use App\Twig\Components\SearchBar;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Search\SearchResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[CoversClass(SearchBar::class)]
final class SearchBarTest extends TestCase
{
    private const string PREFIX = 'test_';

    private Client&MockObject $client;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->requestStack = new RequestStack();
    }

    public function testReturnsEmptyArrayWhenQueryIsEmpty(): void
    {
        $searchBar = $this->createSearchBar();
        $searchBar->query = '';

        $results = $searchBar->getResults();

        self::assertSame([], $results);
        self::assertFalse($searchBar->hasResults());
        self::assertSame(0, $searchBar->getResultCount());
    }

    public function testReturnsEmptyArrayWhenQueryIsSingleCharacter(): void
    {
        $searchBar = $this->createSearchBar();
        $searchBar->query = 'a';

        $results = $searchBar->getResults();

        self::assertSame([], $results);
        self::assertFalse($searchBar->hasResults());
    }

    public function testSearchesWithTwoCharacterQuery(): void
    {
        $this->pushRequest('fr');
        $this->mockSearchReturning([
            ['id' => 1, 'title' => 'Tarte aux pommes'],
        ], estimatedTotalHits: 1);

        $searchBar = $this->createSearchBar();
        $searchBar->query = 'Ta';

        $results = $searchBar->getResults();

        self::assertCount(1, $results);
        self::assertTrue($searchBar->hasResults());
        self::assertSame(1, $searchBar->getResultCount());
    }

    public function testSearchesInCorrectLocaleIndex(): void
    {
        $this->pushRequest('en');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())
            ->method('search')
            ->with('Apple', self::callback(fn (array $options) => $options['locales'] === ['en']))
            ->willReturn($this->createSearchResult([], 0));

        $this->client->expects(self::once())
            ->method('index')
            ->with('test_posts_en')
            ->willReturn($index);

        $searchBar = $this->createSearchBar();
        $searchBar->query = 'Apple';
        $searchBar->getResults();
    }

    public function testDefaultsToFrenchLocaleWhenNoRequest(): void
    {
        // No request pushed to stack

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())
            ->method('search')
            ->willReturn($this->createSearchResult([], 0));

        $this->client->expects(self::once())
            ->method('index')
            ->with('test_posts_fr')
            ->willReturn($index);

        $searchBar = $this->createSearchBar();
        $searchBar->query = 'test';
        $searchBar->getResults();
    }

    public function testReturnsEmptyArrayOnException(): void
    {
        $this->pushRequest('fr');

        $index = $this->createMock(Indexes::class);
        $index->method('search')->willThrowException(new \RuntimeException('Meilisearch error'));

        $this->client->method('index')->willReturn($index);

        $searchBar = $this->createSearchBar();
        $searchBar->query = 'test';

        $results = $searchBar->getResults();

        self::assertSame([], $results);
        self::assertFalse($searchBar->hasResults());
        self::assertSame(0, $searchBar->getResultCount());
    }

    public function testFiltersOnlyActivePosts(): void
    {
        $this->pushRequest('fr');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())
            ->method('search')
            ->with('test', self::callback(fn (array $options) => $options['filter'] === 'isActive = true'))
            ->willReturn($this->createSearchResult([], 0));

        $this->client->method('index')->willReturn($index);

        $searchBar = $this->createSearchBar();
        $searchBar->query = 'test';
        $searchBar->getResults();
    }

    public function testLimitsResultsToFive(): void
    {
        $this->pushRequest('fr');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())
            ->method('search')
            ->with('test', self::callback(fn (array $options) => $options['limit'] === 5))
            ->willReturn($this->createSearchResult([], 0));

        $this->client->method('index')->willReturn($index);

        $searchBar = $this->createSearchBar();
        $searchBar->query = 'test';
        $searchBar->getResults();
    }

    private function createSearchBar(): SearchBar
    {
        return new SearchBar(
            $this->client,
            $this->requestStack,
            self::PREFIX,
        );
    }

    private function pushRequest(string $locale): void
    {
        $request = new Request();
        $request->setLocale($locale);
        $this->requestStack->push($request);
    }

    /**
     * @param array<int, array<string, mixed>> $hits
     */
    private function mockSearchReturning(array $hits, int $estimatedTotalHits): void
    {
        $index = $this->createMock(Indexes::class);
        $index->method('search')->willReturn($this->createSearchResult($hits, $estimatedTotalHits));

        $this->client->method('index')->willReturn($index);
    }

    /**
     * @param array<int, array<string, mixed>> $hits
     */
    private function createSearchResult(array $hits, int $estimatedTotalHits): SearchResult
    {
        return new SearchResult([
            'hits' => $hits,
            'estimatedTotalHits' => $estimatedTotalHits,
            'processingTimeMs' => 1,
            'query' => 'test',
            'offset' => 0,
            'limit' => 5,
        ]);
    }
}
