<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\PostTranslation;
use App\Form\PostTranslationType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Unit tests for PostTranslationType.
 * Tests form structure, field configuration, and data transformation.
 *
 * @internal
 */
final class PostTranslationTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'locale' => 'fr',
            'title' => 'Test Post Title',
            'metaTitle' => 'Test Meta Title',
            'slug' => '', // disabled field, value should be ignored
            'metaDescription' => str_repeat('A', 120),
            'excerpt' => str_repeat('B', 50),
            'notes' => 'Some test notes',
        ];

        $model = new PostTranslation();
        $form = $this->factory->create(PostTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $expected = new PostTranslation();
        $expected->setLocale('fr');
        $expected->setTitle('Test Post Title');
        $expected->setMetaTitle('Test Meta Title');
        $expected->setMetaDescription(str_repeat('A', 120));
        $expected->setExcerpt(str_repeat('B', 50));
        $expected->setNotes('Some test notes');

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($expected, $model);
    }

    public function testSubmitMinimalData(): void
    {
        $formData = [
            'locale' => 'en',
            'title' => 'Minimal Title',
        ];

        $model = new PostTranslation();
        $form = $this->factory->create(PostTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('en', $model->getLocale());
        self::assertSame('Minimal Title', $model->getTitle());
        self::assertSame('', $model->getMetaTitle());
        self::assertSame('', $model->getMetaDescription());
        self::assertSame('', $model->getExcerpt());
        self::assertSame('', $model->getNotes());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(PostTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('locale'));
        self::assertTrue($form->has('title'));
        self::assertTrue($form->has('metaTitle'));
        self::assertTrue($form->has('slug'));
        self::assertTrue($form->has('metaDescription'));
        self::assertTrue($form->has('excerpt'));
        self::assertTrue($form->has('notes'));
    }

    public function testLocaleFieldChoices(): void
    {
        $supportedLocales = ['en', 'fr', 'de'];
        $form = $this->factory->create(PostTranslationType::class, null, [
            'supported_locales' => $supportedLocales,
        ]);

        $view = $form->createView();
        $localeChoices = $view['locale']->vars['choices'];

        self::assertCount(3, $localeChoices);
    }

    public function testSlugFieldIsDisabled(): void
    {
        $form = $this->factory->create(PostTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['slug']->vars['disabled']);
    }

    public function testTitleFieldIsRequired(): void
    {
        $form = $this->factory->create(PostTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['title']->vars['required']);
    }

    public function testOptionalFieldsAreNotRequired(): void
    {
        $form = $this->factory->create(PostTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertFalse($view['metaTitle']->vars['required']);
        self::assertFalse($view['metaDescription']->vars['required']);
        self::assertFalse($view['excerpt']->vars['required']);
        self::assertFalse($view['notes']->vars['required']);
    }

    public function testEmptyDataForTextFields(): void
    {
        $formData = [
            'locale' => 'fr',
            'title' => 'Test',
            'metaTitle' => null,
            'metaDescription' => null,
            'excerpt' => null,
            'notes' => null,
        ];

        $model = new PostTranslation();
        $form = $this->factory->create(PostTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('', $model->getMetaTitle());
        self::assertSame('', $model->getMetaDescription());
        self::assertSame('', $model->getExcerpt());
        self::assertSame('', $model->getNotes());
    }
}
