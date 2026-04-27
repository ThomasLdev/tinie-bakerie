<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\CategoryTranslation;
use App\Form\Type\CategoryTranslationType;
use App\Services\Locale\Locales;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class CategoryTranslationTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        // The locale field is disabled in the form (locale is bound by the
        // parent CRUD via the seeded translations) — pre-set on the model
        // and don't submit it.
        $model = new CategoryTranslation()->setLocale('fr');
        $form = $this->factory->create(CategoryTranslationType::class, $model);

        $form->submit([
            'title' => 'Test Category Title',
            'metaTitle' => 'Test Meta Title',
            'slug' => '', // disabled field, value should be ignored
            'description' => 'Test description content',
            'metaDescription' => str_repeat('A', 120),
            'excerpt' => 'Test excerpt',
        ]);

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
        $model = new CategoryTranslation()->setLocale('en');
        $form = $this->factory->create(CategoryTranslationType::class, $model);

        $form->submit(['title' => 'Minimal Title']);

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
        $form = $this->factory->create(CategoryTranslationType::class);

        self::assertTrue($form->has('locale'));
        self::assertTrue($form->has('title'));
        self::assertTrue($form->has('metaTitle'));
        self::assertTrue($form->has('slug'));
        self::assertTrue($form->has('description'));
        self::assertTrue($form->has('metaDescription'));
        self::assertTrue($form->has('excerpt'));
    }

    public function testLocaleFieldChoicesMatchInjectedLocales(): void
    {
        $form = $this->factory->create(CategoryTranslationType::class);

        self::assertCount(2, $form->createView()['locale']->vars['choices']);
    }

    public function testSlugFieldIsDisabled(): void
    {
        $form = $this->factory->create(CategoryTranslationType::class);

        self::assertTrue($form->createView()['slug']->vars['disabled']);
    }

    public function testTitleFieldIsRequired(): void
    {
        $form = $this->factory->create(CategoryTranslationType::class);

        self::assertTrue($form->createView()['title']->vars['required']);
    }

    public function testOptionalFieldsAreNotRequired(): void
    {
        $form = $this->factory->create(CategoryTranslationType::class);

        $view = $form->createView();

        self::assertFalse($view['metaTitle']->vars['required']);
        self::assertFalse($view['description']->vars['required']);
        self::assertFalse($view['metaDescription']->vars['required']);
        self::assertFalse($view['excerpt']->vars['required']);
    }

    public function testEmptyDataForTextFields(): void
    {
        $model = new CategoryTranslation();
        $form = $this->factory->create(CategoryTranslationType::class, $model);

        $form->submit([
            'locale' => 'fr',
            'title' => 'Test',
            'metaTitle' => null,
            'description' => null,
            'metaDescription' => null,
            'excerpt' => null,
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame('', $model->getMetaTitle());
        self::assertSame('', $model->getDescription());
        self::assertSame('', $model->getMetaDescription());
        self::assertSame('', $model->getExcerpt());
    }

    public function testLocaleFieldIsRequired(): void
    {
        $form = $this->factory->create(CategoryTranslationType::class);

        self::assertTrue($form->createView()['locale']->vars['required']);
    }

    /**
     * @return list<FormExtensionInterface>
     */
    #[\Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([
                new CategoryTranslationType(new Locales('en|fr')),
            ], []),
        ];
    }
}
