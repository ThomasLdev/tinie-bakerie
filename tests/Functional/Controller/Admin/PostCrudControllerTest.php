<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\PostTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
final class PostCrudControllerTest extends KernelTestCase
{
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        
        $this->validator = $container->get(ValidatorInterface::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testPostValidationWithoutCategory(): void
    {
        $post = new Post();
        $post->setCookingTime(30);
        $post->setReadingTime(10);

        $violations = $this->validator->validate($post);

        // Should have validation error for category (NotNull)
        $this->assertGreaterThan(0, $violations->count(), 'Post without category should have validation errors');
        
        $categoryViolation = null;
        foreach ($violations as $violation) {
            if ($violation->getPropertyPath() === 'category') {
                $categoryViolation = $violation;
                break;
            }
        }
        
        $this->assertNotNull($categoryViolation, 'Category validation error should be present');
        $this->assertSame('post.category.not_null', $categoryViolation->getMessageTemplate(), 'Should use correct message template');
    }

    public function testPostValidationWithInvalidReadingTime(): void
    {
        // Get a category for the test
        $category = $this->entityManager->getRepository(Category::class)->findOneBy([]);
        
        if (!$category) {
            $this->markTestSkipped('No categories available for testing. Run fixtures first.');
        }

        $post = new Post();
        $post->setCategory($category);
        $post->setCookingTime(30);
        $post->setReadingTime(500); // Invalid: exceeds 300 max

        $violations = $this->validator->validate($post);

        $this->assertGreaterThan(0, $violations->count(), 'Post with invalid reading time should have validation errors');
        
        $readingTimeViolation = null;
        foreach ($violations as $violation) {
            if ($violation->getPropertyPath() === 'readingTime') {
                $readingTimeViolation = $violation;
                break;
            }
        }
        
        $this->assertNotNull($readingTimeViolation, 'Reading time validation error should be present');
        $this->assertSame('post.reading_time.lte', $readingTimeViolation->getMessageTemplate(), 'Should use correct message template');
    }

    public function testPostValidationWithInvalidCookingTime(): void
    {
        // Get a category for the test
        $category = $this->entityManager->getRepository(Category::class)->findOneBy([]);
        
        if (!$category) {
            $this->markTestSkipped('No categories available for testing. Run fixtures first.');
        }

        $post = new Post();
        $post->setCategory($category);
        $post->setCookingTime(2000); // Invalid: exceeds 1440 max
        $post->setReadingTime(10);

        $violations = $this->validator->validate($post);

        $this->assertGreaterThan(0, $violations->count(), 'Post with invalid cooking time should have validation errors');
        
        $cookingTimeViolation = null;
        foreach ($violations as $violation) {
            if ($violation->getPropertyPath() === 'cookingTime') {
                $cookingTimeViolation = $violation;
                break;
            }
        }
        
        $this->assertNotNull($cookingTimeViolation, 'Cooking time validation error should be present');
        $this->assertSame('post.cooking_time.lte', $cookingTimeViolation->getMessageTemplate(), 'Should use correct message template');
    }

    public function testPostTranslationValidationWithoutTitle(): void
    {
        $translation = new PostTranslation();
        $translation->setLocale('fr');
        // Not setting title - should fail validation

        $violations = $this->validator->validate($translation);

        $this->assertGreaterThan(0, $violations->count(), 'PostTranslation without title should have validation errors');
        
        $titleViolation = null;
        foreach ($violations as $violation) {
            if ($violation->getPropertyPath() === 'title') {
                $titleViolation = $violation;
                break;
            }
        }
        
        $this->assertNotNull($titleViolation, 'Title validation error should be present');
        $this->assertSame('post_translation.title.not_blank', $titleViolation->getMessageTemplate(), 'Should use correct message template');
    }

    public function testPostTranslationValidationWithTooShortTitle(): void
    {
        $translation = new PostTranslation();
        $translation->setLocale('fr');
        $translation->setTitle('AB'); // Too short (min 3)

        $violations = $this->validator->validate($translation);

        $this->assertGreaterThan(0, $violations->count(), 'PostTranslation with short title should have validation errors');
        
        $titleViolation = null;
        foreach ($violations as $violation) {
            if ($violation->getPropertyPath() === 'title' && str_contains($violation->getMessageTemplate(), 'min_length')) {
                $titleViolation = $violation;
                break;
            }
        }
        
        $this->assertNotNull($titleViolation, 'Title min length validation error should be present');
        $this->assertSame('post_translation.title.min_length', $titleViolation->getMessageTemplate(), 'Should use correct message template');
    }

    public function testPostTranslationValidationWithValidData(): void
    {
        $translation = new PostTranslation();
        $translation->setLocale('fr');
        $translation->setTitle('Valid Post Title');

        $violations = $this->validator->validate($translation);

        // Note: May have violations for optional fields like metaDescription, excerpt etc.
        // Just check that title itself is valid (no violations on title property)
        $titleViolations = [];
        foreach ($violations as $violation) {
            if ($violation->getPropertyPath() === 'title') {
                $titleViolations[] = $violation;
            }
        }
        
        $this->assertCount(0, $titleViolations, 'PostTranslation with valid title should have no title validation errors');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
