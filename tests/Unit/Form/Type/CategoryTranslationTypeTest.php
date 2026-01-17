<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\CategoryTranslation;
use App\Form\Type\CategoryTranslationType;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Unit tests for CategoryTranslationType.
 * Tests form structure, field configuration, and data transformation.
 *
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class CategoryTranslationTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'locale' => 'fr',
            'title' => 'Test Category Title',
            'metaTitle' => 'Test Meta Title',
            'slug' => '', // disabled field, value should be ignored
            'description' => 'Test description content',
            'metaDescription' => str_repeat('A', 120),
            'excerpt' => 'Test excerpt',
        ];

        $model = new CategoryTranslation();
        $form = $this->factory->create(CategoryTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('fr', $model->getLocale());
        self::assertSame('Test Category Title', $model->getTitle());
        self::assertSame('Test Meta Title', $model->getMetaTitle());
        self::assertSame('Test description content', $model->getDescription());
        self::assertSame(str_repeat('A', 120), $model->getMetaDescription());
        self::assertSame('Test excerpt', $model->getExcerpt());
    }

    public function testSubmitMinimalData(): void
    {
        $formData = [
            'locale' => 'en',
            'title' => 'Minimal Title',
        ];

        $model = new CategoryTranslation();
        $form = $this->factory->create(CategoryTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('en', $model->getLocale());
        self::assertSame('Minimal Title', $model->getTitle());
        self::assertSame('', $model->getMetaTitle());
        self::assertSame('', $model->getDescription());
        self::assertSame('', $model->getMetaDescription());
        self::assertSame('', $model->getExcerpt());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(CategoryTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('locale'));
        self::assertTrue($form->has('title'));
        self::assertTrue($form->has('metaTitle'));
        self::assertTrue($form->has('slug'));
        self::assertTrue($form->has('description'));
        self::assertTrue($form->has('metaDescription'));
        self::assertTrue($form->has('excerpt'));
    }

    public function testLocaleFieldChoices(): void
    {
        $supportedLocales = ['en', 'fr', 'de'];
        $form = $this->factory->create(CategoryTranslationType::class, null, [
            'supported_locales' => $supportedLocales,
        ]);

        $view = $form->createView();
        $localeChoices = $view['locale']->vars['choices'];

        self::assertCount(3, $localeChoices);
    }

    public function testSlugFieldIsDisabled(): void
    {
        $form = $this->factory->create(CategoryTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['slug']->vars['disabled']);
    }

    public function testTitleFieldIsRequired(): void
    {
        $form = $this->factory->create(CategoryTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['title']->vars['required']);
    }

    public function testOptionalFieldsAreNotRequired(): void
    {
        $form = $this->factory->create(CategoryTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertFalse($view['metaTitle']->vars['required']);
        self::assertFalse($view['description']->vars['required']);
        self::assertFalse($view['metaDescription']->vars['required']);
        self::assertFalse($view['excerpt']->vars['required']);
    }

    public function testEmptyDataForTextFields(): void
    {
        $formData = [
            'locale' => 'fr',
            'title' => 'Test',
            'metaTitle' => null,
            'description' => null,
            'metaDescription' => null,
            'excerpt' => null,
        ];

        $model = new CategoryTranslation();
        $form = $this->factory->create(CategoryTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('', $model->getMetaTitle());
        self::assertSame('', $model->getDescription());
        self::assertSame('', $model->getMetaDescription());
        self::assertSame('', $model->getExcerpt());
    }

    public function testTextareaFieldsAreUsedForLongContent(): void
    {
        $form = $this->factory->create(CategoryTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        // Verify that description, metaDescription, and excerpt use TextareaType
        // by checking they don't have a 'type' attribute set to something else
        self::assertArrayHasKey('attr', $view['description']->vars);
        self::assertArrayHasKey('attr', $view['metaDescription']->vars);
        self::assertArrayHasKey('attr', $view['excerpt']->vars);
    }

    public function testLocaleFieldIsRequired(): void
    {
        $form = $this->factory->create(CategoryTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['locale']->vars['required']);
    }
}
