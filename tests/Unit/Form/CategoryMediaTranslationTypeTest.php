<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\CategoryMediaTranslation;
use App\Form\CategoryMediaTranslationType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Unit tests for CategoryMediaTranslationType.
 *
 * @internal
 */
final class CategoryMediaTranslationTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'locale' => 'fr',
            'title' => 'Test Media Title',
            'alt' => 'Test alt text',
        ];

        $model = new CategoryMediaTranslation();
        $form = $this->factory->create(CategoryMediaTranslationType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('fr', $model->getLocale());
        self::assertSame('Test Media Title', $model->getTitle());
        self::assertSame('Test alt text', $model->getAlt());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(CategoryMediaTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('locale'));
        self::assertTrue($form->has('title'));
        self::assertTrue($form->has('alt'));
    }

    public function testAllFieldsAreRequired(): void
    {
        $form = $this->factory->create(CategoryMediaTranslationType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['locale']->vars['required']);
        self::assertTrue($view['title']->vars['required']);
        self::assertTrue($view['alt']->vars['required']);
    }
}
