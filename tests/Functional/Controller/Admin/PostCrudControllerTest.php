<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Entity\Category;
use App\Entity\Tag;
use App\Repository\PostRepository;
use App\Services\Media\Enum\MediaType;
use App\Services\Post\Enum\Difficulty;
use App\Services\PostSection\Enum\PostSectionType;
use App\Tests\Functional\Controller\Admin\Enum\FormButton;
use App\Tests\Functional\Controller\Admin\Trait\FormTypeTrait;
use App\Tests\Functional\Controller\BaseControllerTestCase;
use App\Tests\Story\PostCrudTestStory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Functional tests for PostCrudController that test from the user perspective.
 * Tests the actual form submission flow including validation, persistence, and EasyAdmin integration.
 *
 * @internal
 */
final class PostCrudControllerTest extends BaseControllerTestCase
{
    use Factories;
    use ResetDatabase;
    use FormTypeTrait;

    private EntityManagerInterface $entityManager;

    private PostRepository $postRepository;

    private PostCrudTestStory $story;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->container->get(EntityManagerInterface::class);
        $this->postRepository = $this->container->get(PostRepository::class);
        $this->story = PostCrudTestStory::load();
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testCreatePostWithValidMinimalData(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();
        $form = $this->getCreateForm($crawler);
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '30',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Test Post Title Minimal FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Test Post Title Minimal EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseRedirects();

        $this->entityManager->clear();
        $post = $this->postRepository->findOneBy(['cookingTime' => 30]);

        self::assertNotNull($post, 'Post should be created in database');
        self::assertTrue($post->isActive());
        self::assertSame(30, $post->getCookingTime());
        self::assertSame(Difficulty::Easy, $post->getDifficulty());
        self::assertSame($category->getId(), $post->getCategory()->getId());

        $translations = $post->getTranslations();
        self::assertCount(2, $translations, 'Post should have two translations (fr and en)');

        $foundFr = false;
        $foundEn = false;

        foreach ($translations as $translation) {
            if ($translation->getTitle() === 'Test Post Title Minimal FR') {
                $foundFr = true;
            }

            if ($translation->getTitle() === 'Test Post Title Minimal EN') {
                $foundEn = true;
            }
        }

        self::assertTrue($foundFr, 'French translation should exist');
        self::assertTrue($foundEn, 'English translation should exist');
    }

    public function testCreatePostWithCompleteData(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '45',
            "{$formName}[difficulty]" => Difficulty::Medium->value,
            "{$formName}[category]" => (string) $category->getId(),
            // French translation
            "{$formName}[translations][0][title]" => 'Complete Test Post FR',
            "{$formName}[translations][0][metaTitle]" => 'Meta Title FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][0][notes]" => 'Some recipe notes in French',
            // English translation
            "{$formName}[translations][1][title]" => 'Complete Test Post EN',
            "{$formName}[translations][1][metaTitle]" => 'Meta Title EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
            "{$formName}[translations][1][notes]" => 'Some recipe notes in English',
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseRedirects();

        $this->entityManager->clear();
        $post = $this->postRepository->findOneBy(['cookingTime' => 45]);

        self::assertNotNull($post, 'Post should be created');
        self::assertSame(45, $post->getCookingTime());
        self::assertSame(Difficulty::Medium, $post->getDifficulty());

        $translations = $post->getTranslations();
        self::assertCount(2, $translations);

        foreach ($translations as $translation) {
            if (str_contains($translation->getTitle(), 'FR')) {
                self::assertSame('Complete Test Post FR', $translation->getTitle());
                self::assertSame('Meta Title FR', $translation->getMetaTitle());
                self::assertStringContainsString('A', $translation->getMetaDescription());
                self::assertStringContainsString('B', $translation->getExcerpt());
                self::assertSame('Some recipe notes in French', $translation->getNotes());
            }
        }
    }

    public function testCreateInactivePost(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form([
            'Post[cookingTime]' => '60',
            'Post[difficulty]' => Difficulty::Advanced->value,
            'Post[category]' => (string) $category->getId(),
            'Post[translations][0][title]' => 'Inactive Post Test FR',
            'Post[translations][0][metaDescription]' => str_repeat('A', 120),
            'Post[translations][0][excerpt]' => str_repeat('B', 50),
            'Post[translations][1][title]' => 'Inactive Post Test EN',
            'Post[translations][1][metaDescription]' => str_repeat('C', 120),
            'Post[translations][1][excerpt]' => str_repeat('D', 50),
        ]);

        $form['Post[active]']->untick();

        $this->client->submit($form);
        self::assertResponseRedirects();

        $this->entityManager->clear();
        $post = $this->postRepository->findOneBy(['cookingTime' => 60]);

        self::assertNotNull($post);
        self::assertFalse($post->isActive(), 'Post should be inactive');
    }

    public function testCreatePostWithoutCategory(): void
    {
        $crawler = $this->loadNewPostForm();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[cookingTime]" => '31',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[translations][0][title]" => 'Test Without Category FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Test Without Category EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseStatusCodeSame(422);
    }

    public function testCreatePostWithInvalidCookingTime(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[cookingTime]" => '2000',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Test Invalid Cooking Time FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Test Invalid Cooking Time EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];

        $this->submitForm($crawler, $formData);
        self::assertResponseStatusCodeSame(422);
    }

    public function testCreatePostWithNegativeCookingTime(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[cookingTime]" => '-10',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Test Negative Cooking Time FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Test Negative Cooking Time EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];

        $this->submitForm($crawler, $formData);
        self::assertResponseStatusCodeSame(422);
    }

    public function testCreatePostWithoutTranslationTitle(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[cookingTime]" => '35',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => '',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Valid Title EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];

        $this->submitForm($crawler, $formData);
        self::assertResponseStatusCodeSame(422);
    }

    public function testCreatePostWithTooShortTranslationTitle(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[cookingTime]" => '40',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'AB',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Valid Title EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];

        $this->submitForm($crawler, $formData);
        self::assertResponseStatusCodeSame(422, 'Form should reject too short title with 422 status');
    }

    public function testCreatePostWithInvalidMetaDescription(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[cookingTime]" => '50',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Test Invalid Meta FR',
            "{$formName}[translations][0][metaDescription]" => 'Too short', // Less than 120 chars - should fail
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Test Invalid Meta EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseStatusCodeSame(422);
    }

    public function testCreatePostWithInvalidExcerpt(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[cookingTime]" => '557',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Test Invalid Excerpt FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => 'Too short', // Less than 50 chars - should fail
            "{$formName}[translations][1][title]" => 'Test Invalid Excerpt EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];

        $this->submitForm($crawler, $formData);
        self::assertResponseStatusCodeSame(422);
    }

    public function testCreatePostWithTags(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();
        $tagIds = $this->getTagIds();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '25',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[tags]" => [(string) $tagIds[0], (string) $tagIds[1]],
            "{$formName}[translations][0][title]" => 'Post With Tags FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Post With Tags EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseRedirects();

        $this->entityManager->clear();
        $post = $this->postRepository->findOneBy(['cookingTime' => 25]);

        self::assertNotNull($post, 'Post should be created');
        self::assertCount(2, $post->getTags(), 'Post should have 2 tags');

        $postTagIds = array_map(fn (Tag $tag) => $tag->getId(), $post->getTags()->toArray());
        self::assertContains($tagIds[0], $postTagIds, 'First tag should be associated');
        self::assertContains($tagIds[1], $postTagIds, 'Second tag should be associated');
    }

    public function testCreatePostWithoutTags(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '26',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Post Without Tags FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Post Without Tags EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseRedirects();

        $this->entityManager->clear();
        $post = $this->postRepository->findOneBy(['cookingTime' => 26]);

        self::assertNotNull($post, 'Post should be created');
        self::assertCount(0, $post->getTags(), 'Post should have no tags');
    }

    public function testCreatePostWithMedia(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '27',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Post With Media FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Post With Media EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
            // First media item
            "{$formName}[media][0][position]" => '0',
            "{$formName}[media][0][type]" => MediaType::Image->value,
            "{$formName}[media][0][translations][0][locale]" => 'fr',
            "{$formName}[media][0][translations][0][alt]" => 'Image alt text FR minimum',
            "{$formName}[media][0][translations][0][title]" => 'Image Title FR',
            "{$formName}[media][0][translations][1][locale]" => 'en',
            "{$formName}[media][0][translations][1][alt]" => 'Image alt text EN minimum',
            "{$formName}[media][0][translations][1][title]" => 'Image Title EN',
            // Second media item
            "{$formName}[media][1][position]" => '1',
            "{$formName}[media][1][type]" => MediaType::Video->value,
            "{$formName}[media][1][translations][0][locale]" => 'fr',
            "{$formName}[media][1][translations][0][alt]" => 'Video alt text FR minimum',
            "{$formName}[media][1][translations][0][title]" => 'Video Title FR',
            "{$formName}[media][1][translations][1][locale]" => 'en',
            "{$formName}[media][1][translations][1][alt]" => 'Video alt text EN minimum',
            "{$formName}[media][1][translations][1][title]" => 'Video Title EN',
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseRedirects();

        $this->entityManager->clear();
        $post = $this->postRepository->findOneBy(['cookingTime' => 27]);

        self::assertNotNull($post, 'Post should be created');
        self::assertCount(2, $post->getMedia(), 'Post should have 2 media items');

        $mediaArray = $post->getMedia()->toArray();
        self::assertSame(MediaType::Image, $mediaArray[0]->getType(), 'First media should be image');
        self::assertSame(MediaType::Video, $mediaArray[1]->getType(), 'Second media should be video');
        self::assertCount(2, $mediaArray[0]->getTranslations(), 'Media should have 2 translations');
    }

    public function testCreatePostWithoutMedia(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '28',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Post Without Media FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Post Without Media EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseRedirects();

        $this->entityManager->clear();
        $post = $this->postRepository->findOneBy(['cookingTime' => 28]);

        self::assertNotNull($post, 'Post should be created');
        self::assertCount(0, $post->getMedia(), 'Post should have no media');
    }

    public function testCreatePostWithInvalidMediaPosition(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '29',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Post With Invalid Media Position FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Post With Invalid Media Position EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
            "{$formName}[media][0][position]" => '-1', // Invalid: negative position
            "{$formName}[media][0][type]" => MediaType::Image->value,
            "{$formName}[media][0][translations][0][alt]" => 'Image alt text FR minimum',
            "{$formName}[media][0][translations][0][title]" => 'Image Title FR',
            "{$formName}[media][0][translations][1][alt]" => 'Image alt text EN minimum',
            "{$formName}[media][0][translations][1][title]" => 'Image Title EN',
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseStatusCodeSame(422);
    }

    public function testCreatePostWithMediaMissingAlt(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '270',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Post With Media Missing Alt FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Post With Media Missing Alt EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
            "{$formName}[media][0][position]" => '0',
            "{$formName}[media][0][type]" => MediaType::Image->value,
            "{$formName}[media][0][translations][0][alt]" => 'A', // Too short: min 5 chars
            "{$formName}[media][0][translations][0][title]" => 'Image Title FR',
            "{$formName}[media][0][translations][1][alt]" => 'Image alt text EN minimum',
            "{$formName}[media][0][translations][1][title]" => 'Image Title EN',
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseStatusCodeSame(422);
    }

    public function testCreatePostWithSections(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '271',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Post With Sections FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Post With Sections EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
            // First section
            "{$formName}[sections][0][position]" => '0',
            "{$formName}[sections][0][type]" => PostSectionType::Default->value,
            "{$formName}[sections][0][translations][0][title]" => 'Section 1 Title FR',
            "{$formName}[sections][0][translations][0][content]" => 'Section 1 content in French',
            "{$formName}[sections][0][translations][1][title]" => 'Section 1 Title EN',
            "{$formName}[sections][0][translations][1][content]" => 'Section 1 content in English',
            // Second section
            "{$formName}[sections][1][position]" => '1',
            "{$formName}[sections][1][type]" => PostSectionType::TwoColumns->value,
            "{$formName}[sections][1][translations][0][title]" => 'Section 2 Title FR',
            "{$formName}[sections][1][translations][0][content]" => 'Section 2 content in French',
            "{$formName}[sections][1][translations][1][title]" => 'Section 2 Title EN',
            "{$formName}[sections][1][translations][1][content]" => 'Section 2 content in English',
            // Third section
            "{$formName}[sections][2][position]" => '2',
            "{$formName}[sections][2][type]" => PostSectionType::TwoColumnsMediaLeft->value,
            "{$formName}[sections][2][translations][0][title]" => 'Section 3 Title FR',
            "{$formName}[sections][2][translations][0][content]" => 'Section 3 content in French',
            "{$formName}[sections][2][translations][1][title]" => 'Section 3 Title EN',
            "{$formName}[sections][2][translations][1][content]" => 'Section 3 content in English',
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseRedirects();

        $this->entityManager->clear();
        $post = $this->postRepository->findOneBy(['cookingTime' => 271]);

        self::assertNotNull($post, 'Post should be created');
        self::assertCount(3, $post->getSections(), 'Post should have 3 sections');

        $sectionsArray = $post->getSections()->toArray();
        self::assertSame(PostSectionType::Default, $sectionsArray[0]->getType(), 'First section should be default type');
        self::assertSame(PostSectionType::TwoColumns, $sectionsArray[1]->getType(), 'Second section should be two columns type');
        self::assertSame(PostSectionType::TwoColumnsMediaLeft, $sectionsArray[2]->getType(), 'Third section should be two columns media left type');
        self::assertCount(2, $sectionsArray[0]->getTranslations(), 'Section should have 2 translations');
    }

    public function testCreatePostWithoutSections(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '272',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Post Without Sections FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Post Without Sections EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseRedirects();

        $this->entityManager->clear();
        $post = $this->postRepository->findOneBy(['cookingTime' => 272]);

        self::assertNotNull($post, 'Post should be created');
        self::assertCount(0, $post->getSections(), 'Post should have no sections');
    }

    public function testCreatePostWithInvalidSectionPosition(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getCategory();

        $form = $crawler->selectButton(FormButton::Create->value)->form();
        $formName = $this->extractFormName($form->getName());

        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '273',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Post With Invalid Section Position FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Post With Invalid Section Position EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
            "{$formName}[sections][0][position]" => '-5', // Invalid: negative position
            "{$formName}[sections][0][type]" => PostSectionType::Default->value,
            "{$formName}[sections][0][translations][0][title]" => 'Section Title FR',
            "{$formName}[sections][0][translations][0][content]" => 'Section content in French',
            "{$formName}[sections][0][translations][1][title]" => 'Section Title EN',
            "{$formName}[sections][0][translations][1][content]" => 'Section content in English',
        ];

        $this->submitForm($crawler, $formData);

        self::assertResponseStatusCodeSame(422);
    }

    private function loadNewPostForm(): Crawler
    {
        return $this->client->request('GET', '/admin/post/new');
    }

    private function getCategory(): Category
    {
        return $this->story->getCategory();
    }

    /**
     * @return int[]
     */
    private function getTagIds(): array
    {
        return array_map(static fn (Tag $tag) => $tag->getId(), $this->story->getAllTags());
    }

    private function submitForm(Crawler $crawler, array $data): void
    {
        $form = $crawler->selectButton(FormButton::Create->value)->form($data);
        $this->client->submit($form);
    }
}
