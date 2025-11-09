<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\PostMediaTranslation;
use App\Form\PostMediaTranslationType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Unit tests for PostMediaTranslationType.
 *
 * @internal
 */
final class PostMediaTranslationTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'locale' => 'en',
            'title' => 'Post Media Title',
            'alt' => 'Post media alt text',
        ];

        $model = new PostMediaTranslation();
        $form = $this->factory->create(PostMediaTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('en', $model->getLocale());
        self::assertSame('Post Media Title', $model->getTitle());
        self::assertSame('Post media alt text', $model->getAlt());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(PostMediaTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('locale'));
        self::assertTrue($form->has('title'));
        self::assertTrue($form->has('alt'));
    }

    public function testAllFieldsAreRequired(): void
    {
        $form = $this->factory->create(PostMediaTranslationType::class, null, [
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

        $model = new PostMediaTranslation();
        $form = $this->factory->create(PostMediaTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('', $model->getTitle());
        self::assertSame('', $model->getAlt());
    }
}
