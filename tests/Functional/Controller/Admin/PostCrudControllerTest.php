<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\PostCrudController;
use App\Entity\Post;
use App\EventSubscriber\KernelRequestSubscriber;
use App\Factory\PostFactory;
use App\Form\PostMediaType;
use App\Form\PostSectionType;
use App\Form\PostTranslationType;
use App\Services\Locale\Locales;
use App\Services\Post\Enum\Difficulty;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Smoke tests for Post CRUD controller in EasyAdmin.
 * Tests all CRUD operations (index, new, edit, detail) for Post entities.
 *
 * @internal
 */
#[CoversClass(PostCrudController::class)]
#[CoversClass(Difficulty::class)]
#[CoversClass(Locales::class)]
#[CoversClass(PostTranslationType::class)]
#[CoversClass(PostMediaType::class)]
#[CoversClass(PostSectionType::class)]
#[CoversClass(KernelRequestSubscriber::class)]
final class PostCrudControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testCategoryEntityName(): void
    {
        self::assertSame(Post::class, PostCrudController::getEntityFqcn());
    }

    public function testIndexPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/admin/post');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href*="/admin/post/new"]');
    }

    public function testIndexPageDisplaysPostList(): void
    {
        PostFactory::createMany(3);

        $this->client->request('GET', '/admin/post');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.table');
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/admin/post/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('GET', "/admin/post/{$post->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testEditPageWithNonExistentPostReturns404(): void
    {
        $this->client->request('GET', '/admin/post/99999/edit');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDetailPageLoadsSuccessfully(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('GET', "/admin/post/{$post->getId()}");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href*="/edit"]');
    }

    public function testDetailPageWithNonExistentPostReturns404(): void
    {
        $this->client->request('GET', '/admin/post/99999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testIndexOnlyAcceptsGetRequests(): void
    {
        $this->client->request('POST', '/admin/post');

        self::assertResponseStatusCodeSame(405);
    }

    public function testDetailOnlyAcceptsGetRequests(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('POST', "/admin/post/{$post->getId()}");

        self::assertResponseStatusCodeSame(405);
    }
}
