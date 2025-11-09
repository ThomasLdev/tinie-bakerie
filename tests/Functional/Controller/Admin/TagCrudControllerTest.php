<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\TagCrudController;
use App\Entity\Tag;
use App\EventSubscriber\KernelRequestSubscriber;
use App\Factory\TagFactory;
use App\Form\TagTranslationType;
use App\Services\Locale\Locales;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Smoke tests for Tag CRUD controller in EasyAdmin.
 * Tests all CRUD operations (index, new, edit, detail) for Tag entities.
 *
 * @internal
 */
#[CoversClass(TagCrudController::class)]
#[CoversClass(Locales::class)]
#[CoversClass(TagTranslationType::class)]
#[CoversClass(KernelRequestSubscriber::class)]
final class TagCrudControllerTest extends WebTestCase
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
        self::assertSame(Tag::class, TagCrudController::getEntityFqcn());
    }

    public function testIndexPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/admin/tag');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href*="/admin/tag/new"]');
    }

    public function testIndexPageDisplaysTagList(): void
    {
        TagFactory::createMany(3);

        $this->client->request('GET', '/admin/tag');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.table');
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/admin/tag/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $tag = TagFactory::createOne();

        $this->client->request('GET', "/admin/tag/{$tag->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testEditPageWithNonExistentTagReturns404(): void
    {
        $this->client->request('GET', '/admin/tag/99999/edit');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDetailPageLoadsSuccessfully(): void
    {
        $tag = TagFactory::createOne();

        $this->client->request('GET', "/admin/tag/{$tag->getId()}");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href*="/edit"]');
    }

    public function testDetailPageWithNonExistentTagReturns404(): void
    {
        $this->client->request('GET', '/admin/tag/99999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testIndexOnlyAcceptsGetRequests(): void
    {
        $this->client->request('POST', '/admin/tag');

        self::assertResponseStatusCodeSame(405);
    }

    public function testDetailOnlyAcceptsGetRequests(): void
    {
        $tag = TagFactory::createOne();

        $this->client->request('POST', "/admin/tag/{$tag->getId()}");

        self::assertResponseStatusCodeSame(405);
    }
}
