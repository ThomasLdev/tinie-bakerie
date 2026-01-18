<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services\Search;

use App\Repository\PostSearchRepository;
use App\Services\Search\PostSearch;
use App\Services\Search\PostSearchResult;
use App\Services\Search\PostSearchResultFactory;
use App\Services\Search\SearchQuerySanitizer;
use App\Tests\Story\PostSearchTestStory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Functional tests for PostSearch service.
 * Tests PostgreSQL Full-Text Search with weighted ranking, ts_headline, and trigram fallback.
 *
 * @internal
 */
#[CoversClass(PostSearch::class)]
#[CoversClass(PostSearchResult::class)]
#[CoversClass(PostSearchResultFactory::class)]
#[CoversClass(PostSearchRepository::class)]
#[CoversClass(SearchQuerySanitizer::class)]
#[Group('search')]
final class PostSearchTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private PostSearch $postSearch;

    private EntityManagerInterface $entityManager;

    private PostSearchTestStory $story;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;

        $postSearch = $container->get(PostSearch::class);
        \assert($postSearch instanceof PostSearch);
        $this->postSearch = $postSearch;
    }

    // ========== Title Search Tests ==========

    #[TestDox('Search by title returns matching post as first result ($locale)')]
    #[DataProvider('provideSearchByTitleData')]
    public function testSearchByTitleReturnsFirstMatch(string $query, string $locale, string $expectedPostKey): void
    {
        $story = $this->loadStoryAndClear();
        $this->setLocale($locale);

        $results = $this->postSearch->search($query, limit: 10);

        self::assertNotEmpty($results, "Search for '{$query}' should return results");
        self::assertSame(
            $story->getExpectedTitle($expectedPostKey, $locale),
            $results[0]->title,
            'First result should be the post with matching title',
        );
    }

    public static function provideSearchByTitleData(): \Generator
    {
        yield 'French: chocolat → Chocolate Cake' => ['chocolat', 'fr', PostSearchTestStory::POST_CHOCOLATE_CAKE];
        yield 'English: chocolate → Chocolate Cake' => ['chocolate', 'en', PostSearchTestStory::POST_CHOCOLATE_CAKE];
        yield 'French: tiramisu → Tiramisu' => ['tiramisu', 'fr', PostSearchTestStory::POST_TIRAMISU];
        yield 'English: tiramisu → Tiramisu' => ['tiramisu', 'en', PostSearchTestStory::POST_TIRAMISU];
        yield 'French: cookies → Cookies' => ['cookies', 'fr', PostSearchTestStory::POST_VEGAN_COOKIES];
        yield 'English: cookies → Cookies' => ['cookies', 'en', PostSearchTestStory::POST_VEGAN_COOKIES];
    }

    // ========== Excerpt Search Tests ==========

    #[TestDox('Search by excerpt content finds matching posts ($locale)')]
    #[DataProvider('provideSearchByExcerptData')]
    public function testSearchByExcerptFindsPost(string $query, string $locale, string $expectedPostKey): void
    {
        $story = $this->loadStoryAndClear();
        $this->setLocale($locale);

        $results = $this->postSearch->search($query, limit: 10);

        self::assertNotEmpty($results, "Search for '{$query}' should return results");
        self::assertContains(
            $story->getExpectedTitle($expectedPostKey, $locale),
            $this->extractTitles($results),
            'Results should contain post with matching excerpt',
        );
    }

    public static function provideSearchByExcerptData(): \Generator
    {
        yield 'French: moelleux (in excerpt) → Chocolate Cake' => ['moelleux', 'fr', PostSearchTestStory::POST_CHOCOLATE_CAKE];
        yield 'English: moist (in excerpt) → Chocolate Cake' => ['moist', 'en', PostSearchTestStory::POST_CHOCOLATE_CAKE];
        yield 'French: croustillants (in excerpt) → Cookies' => ['croustillants', 'fr', PostSearchTestStory::POST_VEGAN_COOKIES];
        yield 'English: crispy (in excerpt) → Cookies' => ['crispy', 'en', PostSearchTestStory::POST_VEGAN_COOKIES];
        yield 'French: italien (in excerpt) → Tiramisu' => ['italien', 'fr', PostSearchTestStory::POST_TIRAMISU];
        yield 'English: italian (in excerpt) → Tiramisu' => ['italian', 'en', PostSearchTestStory::POST_TIRAMISU];
    }

    // ========== Section Content Search Tests ==========

    #[TestDox('Search by section content finds matching posts ($locale)')]
    #[DataProvider('provideSearchBySectionData')]
    public function testSearchBySectionContentFindsPost(string $query, string $locale, string $expectedPostKey): void
    {
        $story = $this->loadStoryAndClear();
        $this->setLocale($locale);

        $results = $this->postSearch->search($query, limit: 10);

        self::assertNotEmpty($results, "Search for '{$query}' should return results");
        self::assertContains(
            $story->getExpectedTitle($expectedPostKey, $locale),
            $this->extractTitles($results),
            'Results should contain post with matching section content',
        );
    }

    public static function provideSearchBySectionData(): \Generator
    {
        yield 'French: mascarpone (in section) → Tiramisu' => ['mascarpone', 'fr', PostSearchTestStory::POST_TIRAMISU];
        yield 'English: mascarpone (in section) → Tiramisu' => ['mascarpone', 'en', PostSearchTestStory::POST_TIRAMISU];
        yield 'French: beurre (in section) → Chocolate Cake' => ['beurre', 'fr', PostSearchTestStory::POST_CHOCOLATE_CAKE];
        yield 'English: butter (in section) → Chocolate Cake' => ['butter', 'en', PostSearchTestStory::POST_CHOCOLATE_CAKE];
        yield 'French: expresso (in section) → Tiramisu' => ['expresso', 'fr', PostSearchTestStory::POST_TIRAMISU];
        yield 'English: espresso (in section) → Tiramisu' => ['espresso', 'en', PostSearchTestStory::POST_TIRAMISU];
    }

    // ========== Tag Search Tests ==========

    #[TestDox('Search by tag name finds matching posts ($locale)')]
    #[DataProvider('provideSearchByTagData')]
    public function testSearchByTagFindsPost(string $query, string $locale, string $expectedPostKey): void
    {
        $story = $this->loadStoryAndClear();
        $this->setLocale($locale);

        $results = $this->postSearch->search($query, limit: 10);

        self::assertNotEmpty($results, "Search for tag '{$query}' should return results");
        self::assertContains(
            $story->getExpectedTitle($expectedPostKey, $locale),
            $this->extractTitles($results),
            'Results should contain post with matching tag',
        );
    }

    public static function provideSearchByTagData(): \Generator
    {
        yield 'French: végétalien tag → Cookies' => ['végétalien', 'fr', PostSearchTestStory::POST_VEGAN_COOKIES];
        yield 'English: vegan tag → Cookies' => ['vegan', 'en', PostSearchTestStory::POST_VEGAN_COOKIES];
    }

    // ========== Category Search Tests ==========

    #[TestDox('Search by category name returns all posts in category')]
    #[DataProvider('provideSearchByCategoryData')]
    public function testSearchByCategoryReturnsAllPosts(string $query, string $locale): void
    {
        $story = $this->loadStoryAndClear();
        $this->setLocale($locale);

        $results = $this->postSearch->search($query, limit: 10);

        self::assertCount(
            $story->getActivePostCount(),
            $results,
            "Search for category '{$query}' should return all active posts",
        );
    }

    public static function provideSearchByCategoryData(): \Generator
    {
        yield 'French: desserts category' => ['desserts', 'fr'];
        yield 'English: desserts category' => ['desserts', 'en'];
    }

    // ========== Inactive Post Exclusion Tests ==========

    #[TestDox('Inactive posts are excluded from search results')]
    #[DataProvider('provideLocaleData')]
    public function testInactivePostsAreExcluded(string $locale): void
    {
        $story = $this->loadStoryAndClear();
        $this->setLocale($locale);

        // "secret" is unique to the inactive post
        $results = $this->postSearch->search('secret', limit: 10);

        self::assertNotContains(
            $story->getExpectedTitle(PostSearchTestStory::POST_INACTIVE, $locale),
            $this->extractTitles($results),
            'Inactive posts should not appear in search results',
        );
    }

    public static function provideLocaleData(): \Generator
    {
        yield 'French locale' => ['fr'];
        yield 'English locale' => ['en'];
    }

    // ========== Edge Cases ==========

    #[TestDox('Empty or whitespace query returns no results')]
    #[DataProvider('provideEmptyQueryData')]
    public function testEmptyQueryReturnsNoResults(string $query): void
    {
        $this->loadStoryAndClear();
        $this->setLocale('fr');

        $results = $this->postSearch->search($query, limit: 10);

        self::assertEmpty($results, 'Empty or whitespace query should return no results');
    }

    public static function provideEmptyQueryData(): \Generator
    {
        yield 'Empty string' => [''];
        yield 'Single space' => [' '];
        yield 'Multiple spaces' => ['   '];
        yield 'Tab character' => ["\t"];
    }

    #[TestDox('Unknown query returns no results')]
    public function testUnknownQueryReturnsNoResults(): void
    {
        $this->loadStoryAndClear();
        $this->setLocale('fr');

        $results = $this->postSearch->search('xyzzyznonexistent', limit: 10);

        self::assertEmpty($results, 'Search for non-existent term should return no results');
    }

    #[TestDox('Single character query is handled gracefully')]
    public function testSingleCharacterQueryIsHandled(): void
    {
        $this->loadStoryAndClear();
        $this->setLocale('fr');

        // Should not throw exception - just verify it returns without error
        $results = $this->postSearch->search('a', limit: 10);

        // Results may be empty or not, but the call should succeed
        self::assertGreaterThanOrEqual(0, \count($results));
    }

    // ========== Result Structure Tests ==========

    #[TestDox('Search result contains all expected fields')]
    public function testResultContainsExpectedFields(): void
    {
        $this->loadStoryAndClear();
        $this->setLocale('fr');

        $results = $this->postSearch->search('chocolat', limit: 10);

        self::assertNotEmpty($results);
        $result = $results[0];

        // Verify all fields have meaningful values
        self::assertGreaterThan(0, $result->id);
        self::assertNotEmpty($result->title);
        self::assertNotEmpty($result->slug);
        self::assertNotEmpty($result->excerpt);
        self::assertNotEmpty($result->categoryTitle);
        self::assertNotEmpty($result->categorySlug);
        self::assertGreaterThan(0.0, $result->rank);
    }

    #[TestDox('Limit parameter restricts result count')]
    public function testLimitParameterRestrictsResults(): void
    {
        $this->loadStoryAndClear();
        $this->setLocale('fr');

        $results = $this->postSearch->search('desserts', limit: 2);

        self::assertLessThanOrEqual(2, \count($results), 'Results should respect limit parameter');
    }

    // ========== Multi-word and Partial Search Tests ==========

    #[TestDox('Multi-word search returns matching results')]
    public function testMultiWordSearch(): void
    {
        $story = $this->loadStoryAndClear();
        $this->setLocale('fr');

        $results = $this->postSearch->search('gateau chocolat', limit: 10);

        self::assertNotEmpty($results, 'Multi-word search should return results');
        self::assertSame(
            $story->getExpectedTitle(PostSearchTestStory::POST_CHOCOLATE_CAKE, 'fr'),
            $results[0]->title,
        );
    }

    #[TestDox('Partial word search (prefix) returns matching results')]
    public function testPartialWordSearchWithPrefix(): void
    {
        $story = $this->loadStoryAndClear();
        $this->setLocale('en');

        $results = $this->postSearch->search('choco', limit: 10);

        self::assertNotEmpty($results, 'Partial word search should return results');
        self::assertContains(
            $story->getExpectedTitle(PostSearchTestStory::POST_CHOCOLATE_CAKE, 'en'),
            $this->extractTitles($results),
        );
    }

    // ========== Ranking Tests ==========

    #[TestDox('Title matches rank higher than section content matches')]
    public function testTitleMatchesRankHigher(): void
    {
        $story = $this->loadStoryAndClear();
        $this->setLocale('fr');

        // "chocolat" appears in title (weight A) and section (weight C)
        $results = $this->postSearch->search('chocolat', limit: 10);

        self::assertNotEmpty($results);
        // First result should be the one with title match
        self::assertSame(
            $story->getExpectedTitle(PostSearchTestStory::POST_CHOCOLATE_CAKE, 'fr'),
            $results[0]->title,
            'Post with title match should rank first',
        );
    }

    // ========== Locale Isolation Tests ==========

    #[TestDox('Search results respect current locale for slugs')]
    #[DataProvider('provideLocaleIsolationData')]
    public function testSearchResultsRespectLocale(string $locale, string $expectedSlugPattern, string $unexpectedSlugPattern): void
    {
        $this->loadStoryAndClear();
        $this->setLocale($locale);

        $results = $this->postSearch->search('desserts', limit: 10);

        foreach ($results as $result) {
            self::assertStringContainsString(
                $expectedSlugPattern,
                $result->categorySlug,
                "Category slug should match {$locale} locale pattern",
            );
            self::assertStringNotContainsString(
                $unexpectedSlugPattern,
                $result->categorySlug,
                'Category slug should not contain other locale pattern',
            );
        }
    }

    public static function provideLocaleIsolationData(): \Generator
    {
        yield 'French locale returns French slugs' => ['fr', 'gourmands', 'gourmet'];
        yield 'English locale returns English slugs' => ['en', 'gourmet', 'gourmands'];
    }

    private function setLocale(string $locale): void
    {
        $requestStack = self::getContainer()->get(RequestStack::class);
        \assert($requestStack instanceof RequestStack);

        $request = new Request();
        $request->setLocale($locale);
        $requestStack->push($request);
    }

    private function loadStoryAndClear(): PostSearchTestStory
    {
        $this->story = PostSearchTestStory::load();
        $this->entityManager->clear();

        return $this->story;
    }

    /**
     * Extract titles from search results.
     *
     * @param PostSearchResult[] $results
     *
     * @return string[]
     */
    private function extractTitles(array $results): array
    {
        return array_map(static fn (PostSearchResult $r): string => $r->title, $results);
    }
}
