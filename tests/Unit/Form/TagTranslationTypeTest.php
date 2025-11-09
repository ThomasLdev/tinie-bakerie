<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\TagTranslation;
use App\Form\TagTranslationType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Unit tests for TagTranslationType.
 * Tests form structure, field configuration, and data transformation.
 *
 * @internal
 */
final class TagTranslationTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'locale' => 'fr',
            'title' => 'Test Tag Title',
        ];

        $model = new TagTranslation();
        $form = $this->factory->create(TagTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('fr', $model->getLocale());
        self::assertSame('Test Tag Title', $model->getTitle());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(TagTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('locale'));
        self::assertTrue($form->has('title'));
        self::assertFalse($form->has('slug'), 'TagTranslation should not have slug field');
    }

    public function testLocaleFieldChoices(): void
    {
        $supportedLocales = ['en', 'fr', 'de'];
        $form = $this->factory->create(TagTranslationType::class, null, [
            'supported_locales' => $supportedLocales,
        ]);

        $view = $form->createView();
        $localeChoices = $view['locale']->vars['choices'];

        self::assertCount(3, $localeChoices);
    }

    public function testLocaleFieldIsRequired(): void
    {
        $form = $this->factory->create(TagTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['locale']->vars['required']);
    }

    public function testTitleFieldIsRequired(): void
    {
        $form = $this->factory->create(TagTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['title']->vars['required']);
    }

    public function testFormStructureMatchesEntity(): void
    {
        $formData = [
            'locale' => 'en',
            'title' => 'English Tag',
        ];

        $model = new TagTranslation();
        $form = $this->factory->create(TagTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isValid());
        self::assertSame('en', $model->getLocale());
        self::assertSame('English Tag', $model->getTitle());
    }
}
