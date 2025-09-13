<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\CategoryController;
use App\Repository\CategoryRepository;
use App\Services\Cache\CategoryCache;
use Doctrine\ORM\EntityManagerInterface;
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
    private CategoryRepository $categoryRepository;

    private EntityManagerInterface $entityManager;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public static function getCategoryControllerData(): \Generator
    {
        yield 'fr categories index page' => ['fr', '/fr/categories'];

        yield 'en categories index page' => ['en', '/en/categories'];
    }

    #[DataProvider('getCategoryControllerData')]
    public function testIndex(string $locale, string $baseUrl): void
    {
        $this->entityManager->getFilters()->enable('locale_filter')->setParameter('locale', $locale);

        $this->client->request(Request::METHOD_GET, $baseUrl);

        self::assertResponseIsSuccessful();
    }

    #[DataProvider('getCategoryControllerData')]
    public function testShowWithFoundCategory(string $locale, string $baseUrl): void
    {
        $this->entityManager->getFilters()->enable('locale_filter')->setParameter('locale', $locale);

        $categories = $this->categoryRepository->findAll();

        self::assertNotEmpty($categories);

        $category = $categories[array_rand($categories)];

        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s', $baseUrl, $category->getSlug()),
        );

        self::assertResponseIsSuccessful();

        $title = $crawler
            ->filter(\sprintf('html:contains("%s")', $category->getTitle()))
            ->getNode(0)
            ->textContent;

        self::assertStringContainsString($category->getTitle(), $title);
    }

    /**
     * Note: Tests that expect 404 responses will show "NotFoundHttpException"
     * error messages in the output. This is expected behavior as Symfony logs
     * exceptions before converting them to HTTP responses.
     */
    public function testShowWithNotFoundCategory(): void
    {
        $this->client->request(Request::METHOD_GET, '/fr/categories/unknown-category');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[DataProvider('getCategoryControllerData')]
    public function testIndexWithLocaleFilter(string $locale, string $baseUrl): void
    {
        $this->entityManager->getFilters()->enable('locale_filter')->setParameter('locale', $locale);

        $this->client->request(Request::METHOD_GET, $baseUrl);

        self::assertResponseIsSuccessful();
    }

    public function testShowCategoryWithInvalidSlug(): void
    {
        $this->client->request(Request::METHOD_GET, '/fr/categories/invalid-slug-123');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testShowCategoryWithEmptySlug(): void
    {
        $this->client->request(Request::METHOD_GET, '/fr/categories/');

        // This should either be successful (index) or redirect, depending on routing configuration
        self::assertTrue(
            $this->client->getResponse()->isSuccessful()
            || $this->client->getResponse()->isRedirection(),
        );
    }
}
