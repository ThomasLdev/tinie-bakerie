<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Services\Post\Enum\Difficulty;
use App\Tests\Functional\Controller\BaseControllerTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Functional tests for PostCrudController that test from the user perspective.
 * Tests the actual form submission flow including validation, persistence, and EasyAdmin integration.
 *
 * @internal
 */
final class PostCrudControllerTest extends BaseControllerTestCase
{
    private EntityManagerInterface $entityManager;
    private PostRepository $postRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $container = self::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->postRepository = $container->get(PostRepository::class);
    }

    // ========================================
    // FORM RENDERING TESTS
    // ========================================

    public function testNewPostFormLoadsSuccessfully(): void
    {
        $crawler = $this->loadNewPostForm();
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form', 'Form should be present on the page');
    }

    public function testNewPostFormContainsRequiredFields(): void
    {
        $crawler = $this->loadNewPostForm();
        
        $this->assertResponseIsSuccessful();
        
        // Check that essential form fields exist
        $this->assertSelectorExists('input[name*="[active]"]', 'Active field should exist');
        $this->assertSelectorExists('input[name*="[cookingTime]"]', 'Cooking time field should exist');
        $this->assertSelectorExists('input[name*="[difficulty]"]', 'Difficulty field should exist (as radio buttons)');
        $this->assertSelectorExists('select[name*="[category]"]', 'Category field should exist');
        $this->assertSelectorExists('input[name*="[translations]"]', 'Translation fields should exist');
    }

    // ========================================
    // SUCCESSFUL CREATION TESTS
    // ========================================

    public function testCreatePostWithValidMinimalData(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getRandomCategory();
        
        // Extract form field names dynamically
        $form = $crawler->selectButton('Créer')->form();
        $formName = $this->extractFormName($form->getName());
        
        // EasyAdmin pre-creates translations for all locales (fr and en)
        // We need to fill required fields for both to pass validation
        // Note: metaDescription (min 120) and excerpt (min 50) are validated even when optional
        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '30',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            // French translation (index 0)
            "{$formName}[translations][0][title]" => 'Test Post Title Minimal FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120), // Min 120 chars
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50), // Min 50 chars
            // English translation (index 1)  
            "{$formName}[translations][1][title]" => 'Test Post Title Minimal EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120), // Min 120 chars
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50), // Min 50 chars
        ];
        
        $this->submitPostForm($crawler, $formData);
        
        // Should redirect after successful creation
        $this->assertResponseRedirects();
        
        // Verify post was created in database
        $this->entityManager->clear(); // Clear to force fresh query
        $post = $this->postRepository->findOneBy(['cookingTime' => 30]);
        
        $this->assertNotNull($post, 'Post should be created in database');
        $this->assertTrue($post->isActive());
        $this->assertSame(30, $post->getCookingTime());
        $this->assertSame(Difficulty::Easy, $post->getDifficulty());
        $this->assertSame($category->getId(), $post->getCategory()->getId());
        
        // Verify translations
        $translations = $post->getTranslations();
        $this->assertCount(2, $translations, 'Post should have two translations (fr and en)');
        
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
        $this->assertTrue($foundFr, 'French translation should exist');
        $this->assertTrue($foundEn, 'English translation should exist');
        
        // Cleanup
        $this->cleanupPost($post);
    }

    public function testCreatePostWithCompleteData(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getRandomCategory();
        
        $form = $crawler->selectButton('Créer')->form();
        $formName = $this->extractFormName($form->getName());
        
        // Fill complete data for both locales
        $formData = [
            "{$formName}[active]" => '1',
            "{$formName}[cookingTime]" => '45',
            "{$formName}[difficulty]" => Difficulty::Medium->value,
            "{$formName}[category]" => (string) $category->getId(),
            // French translation
            "{$formName}[translations][0][title]" => 'Complete Test Post FR',
            "{$formName}[translations][0][metaTitle]" => 'Meta Title FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120), // Min 120 chars
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50), // Min 50 chars
            "{$formName}[translations][0][notes]" => 'Some recipe notes in French',
            // English translation
            "{$formName}[translations][1][title]" => 'Complete Test Post EN',
            "{$formName}[translations][1][metaTitle]" => 'Meta Title EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
            "{$formName}[translations][1][notes]" => 'Some recipe notes in English',
        ];
        
        $this->submitPostForm($crawler, $formData);
        
        $this->assertResponseRedirects();
        
        // Verify in database
        $this->entityManager->clear();
        $post = $this->postRepository->findOneBy(['cookingTime' => 45]);
        
        $this->assertNotNull($post, 'Post should be created');
        $this->assertSame(45, $post->getCookingTime());
        $this->assertSame(Difficulty::Medium, $post->getDifficulty());
        
        // Verify translation data
        $translations = $post->getTranslations();
        $this->assertCount(2, $translations);
        
        foreach ($translations as $translation) {
            if (str_contains($translation->getTitle(), 'FR')) {
                $this->assertSame('Complete Test Post FR', $translation->getTitle());
                $this->assertSame('Meta Title FR', $translation->getMetaTitle());
                $this->assertStringContainsString('A', $translation->getMetaDescription());
                $this->assertStringContainsString('B', $translation->getExcerpt());
                $this->assertSame('Some recipe notes in French', $translation->getNotes());
            }
        }
        
        // Cleanup
        $this->cleanupPost($post);
    }

    public function testCreateInactivePost(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getRandomCategory();
        
        $form = $crawler->selectButton('Créer')->form([
            // Don't include active field to leave it unchecked (defaults to inactive)
            "Post[cookingTime]" => '60',
            "Post[difficulty]" => Difficulty::Advanced->value,
            "Post[category]" => (string) $category->getId(),
            "Post[translations][0][title]" => 'Inactive Post Test FR',
            "Post[translations][0][metaDescription]" => str_repeat('A', 120),
            "Post[translations][0][excerpt]" => str_repeat('B', 50),
            "Post[translations][1][title]" => 'Inactive Post Test EN',
            "Post[translations][1][metaDescription]" => str_repeat('C', 120),
            "Post[translations][1][excerpt]" => str_repeat('D', 50),
        ]);
        
        // Uncheck the active checkbox
        $form['Post[active]']->untick();
        
        $this->client->submit($form);
        $this->assertResponseRedirects();
        
        $this->entityManager->clear();
        $post = $this->postRepository->findOneBy(['cookingTime' => 60]);
        
        $this->assertNotNull($post);
        $this->assertFalse($post->isActive(), 'Post should be inactive');
        
        // Cleanup
        $this->cleanupPost($post);
    }

    // ========================================
    // VALIDATION FAILURE TESTS
    // ========================================

    public function testCreatePostWithoutCategory(): void
    {
        $crawler = $this->loadNewPostForm();
        
        $form = $crawler->selectButton('Créer')->form();
        $formName = $this->extractFormName($form->getName());
        
        // Submit without category (but with valid translations)
        $formData = [
            "{$formName}[cookingTime]" => '31',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[translations][0][title]" => 'Test Without Category FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Test Without Category EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
            // Intentionally not setting category
        ];
        
        $this->submitPostForm($crawler, $formData);
        
        // Should return 422 due to validation error
        $this->assertResponseStatusCodeSame(422);
        
        // Check no post was created
        $posts = $this->postRepository->findBy(['cookingTime' => 31]);
        $this->assertEmpty($posts, 'No post should be created without category');
    }

    public function testCreatePostWithInvalidCookingTime(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getRandomCategory();
        
        $form = $crawler->selectButton('Créer')->form();
        $formName = $this->extractFormName($form->getName());
        
        // Submit with cooking time > 1440 (max allowed)
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
        
        $this->submitPostForm($crawler, $formData);
        
        // Should return 422
        $this->assertResponseStatusCodeSame(422);
        
        // Verify nothing was saved
        $posts = $this->postRepository->findBy(['cookingTime' => 2000]);
        $this->assertEmpty($posts, 'No post should be created with invalid cooking time');
    }

    public function testCreatePostWithNegativeCookingTime(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getRandomCategory();
        
        $form = $crawler->selectButton('Créer')->form();
        $formName = $this->extractFormName($form->getName());
        
        // Submit with negative cooking time
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
        
        $this->submitPostForm($crawler, $formData);
        
        $this->assertResponseStatusCodeSame(422);
        
        // Verify nothing was saved with negative cooking time
        $posts = $this->postRepository->findAll();
        foreach ($posts as $post) {
            $this->assertGreaterThanOrEqual(0, $post->getCookingTime(), 'All posts should have non-negative cooking time');
        }
    }

    public function testCreatePostWithoutTranslationTitle(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getRandomCategory();
        
        $form = $crawler->selectButton('Créer')->form();
        $formName = $this->extractFormName($form->getName());
        
        // Submit without translation title (one translation has empty title)
        $formData = [
            "{$formName}[cookingTime]" => '35',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => '', // Empty title - should fail
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Valid Title EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];
        
        $this->submitPostForm($crawler, $formData);
        
        // Should return 422
        $this->assertResponseStatusCodeSame(422);
        
        // Verify nothing was saved
        $posts = $this->postRepository->findBy(['cookingTime' => 35]);
        $this->assertEmpty($posts, 'No post should be created without translation title');
    }

    public function testCreatePostWithTooShortTranslationTitle(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getRandomCategory();
        
        $form = $crawler->selectButton('Créer')->form();
        $formName = $this->extractFormName($form->getName());
        
        // Submit with title too short (min 3 chars)
        $formData = [
            "{$formName}[cookingTime]" => '40',
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'AB', // Only 2 chars - should fail
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => str_repeat('B', 50),
            "{$formName}[translations][1][title]" => 'Valid Title EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];
        
        $this->submitPostForm($crawler, $formData);
        
        $this->assertResponseStatusCodeSame(422);
        
        // Verify nothing was saved
        $posts = $this->postRepository->findBy(['cookingTime' => 40]);
        $this->assertEmpty($posts, 'No post should be created with too short title');
    }

    public function testCreatePostWithInvalidMetaDescription(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getRandomCategory();
        
        $form = $crawler->selectButton('Créer')->form();
        $formName = $this->extractFormName($form->getName());
        
        // Submit with meta description too short (min 120)
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
        
        $this->submitPostForm($crawler, $formData);
        
        $this->assertResponseStatusCodeSame(422);
        
        // Verify nothing was saved
        $posts = $this->postRepository->findBy(['cookingTime' => 50]);
        $this->assertEmpty($posts, 'No post should be created with invalid meta description');
    }

    public function testCreatePostWithInvalidExcerpt(): void
    {
        $crawler = $this->loadNewPostForm();
        $category = $this->getRandomCategory();
        
        $form = $crawler->selectButton('Créer')->form();
        $formName = $this->extractFormName($form->getName());
        
        // Submit with excerpt too short (min 50)
        $formData = [
            "{$formName}[cookingTime]" => '557', // Unique value
            "{$formName}[difficulty]" => Difficulty::Easy->value,
            "{$formName}[category]" => (string) $category->getId(),
            "{$formName}[translations][0][title]" => 'Test Invalid Excerpt FR',
            "{$formName}[translations][0][metaDescription]" => str_repeat('A', 120),
            "{$formName}[translations][0][excerpt]" => 'Too short', // Less than 50 chars - should fail
            "{$formName}[translations][1][title]" => 'Test Invalid Excerpt EN',
            "{$formName}[translations][1][metaDescription]" => str_repeat('C', 120),
            "{$formName}[translations][1][excerpt]" => str_repeat('D', 50),
        ];
        
        $this->submitPostForm($crawler, $formData);
        
        $this->assertResponseStatusCodeSame(422);
        
        // Verify nothing was saved
        $posts = $this->postRepository->findBy(['cookingTime' => 557]);
        $this->assertEmpty($posts, 'No post should be created with invalid excerpt');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    private function loadNewPostForm(): Crawler
    {
        return $this->client->request('GET', '/admin/post/new');
    }

    private function getRandomCategory(): Category
    {
        $category = $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy([]);
            
        if (!$category) {
            $this->markTestSkipped('No categories available. Please run fixtures first.');
        }
        
        return $category;
    }

    private function submitPostForm(Crawler $crawler, array $data): void
    {
        // EasyAdmin uses "Créer" (Create in French) as the button text
        $form = $crawler->selectButton('Créer')->form($data);
        $this->client->submit($form);
    }

    private function extractFormName(string $fullFormName): string
    {
        // Extract form name from something like "Post[field]" -> "Post"
        if (preg_match('/^([^\[]+)/', $fullFormName, $matches)) {
            return $matches[1];
        }
        
        return 'Post';
    }

    private function cleanupPost(Post $post): void
    {
        // Remove the test post from database
        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
