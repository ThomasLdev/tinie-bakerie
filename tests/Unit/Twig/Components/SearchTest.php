<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig\Components;

use App\Services\Search\PostSearch;
use App\Services\Search\PostSearchResult;
use App\Twig\Components\Search;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Search::class)]
final class SearchTest extends TestCase
{
    #[TestDox('Returns empty array when query is shorter than 2 characters')]
    #[DataProvider('provideShortQueries')]
    public function testReturnsEmptyForShortQuery(string $query): void
    {
        $postSearch = $this->createMock(PostSearch::class);
        $postSearch->expects($this->never())->method('search');

        $component = new Search($postSearch);
        $component->query = $query;

        self::assertSame([], $component->getResults());
    }

    public static function provideShortQueries(): \Generator
    {
        yield 'empty string' => [''];
        yield 'single character' => ['a'];
        yield 'single space' => [' '];
    }

    #[TestDox('Calls PostSearch with query and limit when query has 2+ characters')]
    public function testCallsPostSearchForValidQuery(): void
    {
        $expectedResults = [
            new PostSearchResult(
                id: 1,
                title: 'Chocolate Cake',
                slug: 'chocolate-cake',
                excerpt: 'A delicious cake',
                categoryTitle: 'Desserts',
                categorySlug: 'desserts',
                mediaPath: null,
                rank: 1.0,
                headline: null,
            ),
        ];

        $postSearch = $this->createMock(PostSearch::class);
        $postSearch->expects($this->once())
            ->method('search')
            ->with('chocolate', 15)
            ->willReturn($expectedResults);

        $component = new Search($postSearch);
        $component->query = 'chocolate';

        self::assertSame($expectedResults, $component->getResults());
    }

    #[TestDox('Handles multibyte characters correctly (2 chars = valid)')]
    public function testHandlesMultibyteCharacters(): void
    {
        $postSearch = $this->createMock(PostSearch::class);
        $postSearch->expects($this->once())
            ->method('search')
            ->with('日本', 15)
            ->willReturn([]);

        $component = new Search($postSearch);
        $component->query = '日本';

        $component->getResults();
    }

    #[TestDox('Single multibyte character is too short')]
    public function testSingleMultibyteCharacterIsTooShort(): void
    {
        $postSearch = $this->createMock(PostSearch::class);
        $postSearch->expects($this->never())->method('search');

        $component = new Search($postSearch);
        $component->query = '日';

        self::assertSame([], $component->getResults());
    }
}
