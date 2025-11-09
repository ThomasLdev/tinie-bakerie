<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\PostSectionTranslation;
use App\Form\PostSectionTranslationType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Unit tests for PostSectionTranslationType.
 *
 * @internal
 */
final class PostSectionTranslationTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'locale' => 'en',
            'title' => 'Section Title',
            'content' => 'Section content goes here',
        ];

        $model = new PostSectionTranslation();
        $form = $this->factory->create(PostSectionTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('en', $model->getLocale());
        self::assertSame('Section Title', $model->getTitle());
        self::assertSame('Section content goes here', $model->getContent());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(PostSectionTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('locale'));
        self::assertTrue($form->has('title'));
        self::assertTrue($form->has('content'));
    }

    public function testAllFieldsAreRequired(): void
    {
        $form = $this->factory->create(PostSectionTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['locale']->vars['required']);
        self::assertTrue($view['title']->vars['required']);
        self::assertTrue($view['content']->vars['required']);
    }

    public function testEmptyDataForTextFields(): void
    {
        $formData = [
            'locale' => 'fr',
            'title' => '',
            'content' => '',
        ];

        $model = new PostSectionTranslation();
        $form = $this->factory->create(PostSectionTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('', $model->getTitle());
        self::assertSame('', $model->getContent());
    }
}
