<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\PostSectionMediaTranslation;
use App\Form\PostSectionMediaTranslationType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Unit tests for PostSectionMediaTranslationType.
 *
 * @internal
 */
final class PostSectionMediaTranslationTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'locale' => 'en',
            'title' => 'Section Media Title',
            'alt' => 'Section media alt text',
        ];

        $model = new PostSectionMediaTranslation();
        $form = $this->factory->create(PostSectionMediaTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('en', $model->getLocale());
        self::assertSame('Section Media Title', $model->getTitle());
        self::assertSame('Section media alt text', $model->getAlt());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(PostSectionMediaTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('locale'));
        self::assertTrue($form->has('title'));
        self::assertTrue($form->has('alt'));
    }

    public function testAllFieldsAreRequired(): void
    {
        $form = $this->factory->create(PostSectionMediaTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['locale']->vars['required']);
        self::assertTrue($view['title']->vars['required']);
        self::assertTrue($view['alt']->vars['required']);
    }

    public function testEmptyDataForTextFields(): void
    {
        $formData = [
            'locale' => 'fr',
            'title' => '',
            'alt' => '',
        ];

        $model = new PostSectionMediaTranslation();
        $form = $this->factory->create(PostSectionMediaTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('', $model->getTitle());
        self::assertSame('', $model->getAlt());
    }
}
