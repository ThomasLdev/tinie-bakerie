<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\CategoryController;
use App\Repository\CategoryRepository;
use App\Services\Cache\CategoryCache;
use App\Tests\Story\CategoryControllerTestStory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(CategoryController::class)]
#[CoversClass(CategoryRepository::class)]
#[CoversClass(CategoryCache::class)]
final class CategoryControllerTest extends BaseControllerTestCase
{
    private const string BASE_URL_FR = '/fr/categories';

    private const string BASE_URL_EN = '/en/categories';

    #[DataProvider('getCategoryControllerShowData')]
    public function testShowWithFoundCategory(string $expectedTitle, string $locale, string $baseUrl): void
    {
        /** @var CategoryControllerTestStory $story */
        $story = $this->loadStory(fn() => CategoryControllerTestStory::load());
        $category = $story->getCategory(0);
        $categorySlug = $story->getCategorySlug($category, $locale);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s', $baseUrl, $categorySlug),
        );

        self::assertResponseIsSuccessful();
        
        // Verify the page title contains the category title
        $pageTitle = $crawler->filter('title')->text();
        self::assertStringContainsString($expectedTitle, $pageTitle, 'Page title should contain category title');
        
        // Verify the category title appears in the page content
        $html = $crawler->html();
        self::assertStringContainsString($expectedTitle, $html, 'Category title should be present in page content');
    }

    public function testShowWithNotFoundCategory(): void
    {
        foreach ([self::BASE_URL_FR, self::BASE_URL_EN] as $baseUrl) {
            $this->client->request(
                Request::METHOD_GET,
                \sprintf('%s/unknown-category', $baseUrl)
            );

            self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[DataProvider('getHttpMethodsData')]
    public function testShowRejectsNonGetMethods(string $method, string $baseUrl): void
    {
        /** @var CategoryControllerTestStory $story */
        $story = $this->loadStory(fn() => CategoryControllerTestStory::load());
        $category = $story->getCategory(0);
        $locale = $baseUrl === self::BASE_URL_FR ? 'fr' : 'en';
        $categorySlug = $story->getCategorySlug($category, $locale);

        $this->client->request(
            $method,
            \sprintf('%s/%s', $baseUrl, $categorySlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public static function getCategoryControllerShowData(): \Generator
    {
        yield 'should find fr title on category fr page' => ['Catégorie Test 1 FR', 'fr', self::BASE_URL_FR];

        yield 'should find en title on category en page' => ['Test Category 1 EN', 'en', self::BASE_URL_EN];
    }

    public static function getHttpMethodsData(): \Generator
    {
        yield 'POST method on fr url' => [Request::METHOD_POST, self::BASE_URL_FR];

        yield 'POST method on en url' => [Request::METHOD_POST, self::BASE_URL_EN];

        yield 'PUT method on fr url' => [Request::METHOD_PUT, self::BASE_URL_FR];

        yield 'PUT method on en url' => [Request::METHOD_PUT, self::BASE_URL_EN];

        yield 'DELETE method on fr url' => [Request::METHOD_DELETE, self::BASE_URL_FR];

        yield 'DELETE method on en url' => [Request::METHOD_DELETE, self::BASE_URL_EN];

        yield 'PATCH method on fr url' => [Request::METHOD_PATCH, self::BASE_URL_FR];

        yield 'PATCH method on en url' => [Request::METHOD_PATCH, self::BASE_URL_EN];
    }
}
