<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\PostTranslation;
use App\Form\Type\PostTranslationType;
use App\Services\Locale\Locales;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @internal
 */
#[CoversClass(PostTranslationType::class)]
#[AllowMockObjectsWithoutExpectations]
final class PostTranslationTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $model = new PostTranslation()->setLocale('fr');
        $form = $this->factory->create(PostTranslationType::class, $model);

        $form->submit([
            'title' => 'Test Post Title',
            'metaTitle' => 'Test Meta Title',
            'slug' => '', // disabled field, value should be ignored
            'metaDescription' => str_repeat('A', 120),
            'excerpt' => str_repeat('B', 50),
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame('fr', $model->getLocale());
        self::assertSame('Test Post Title', $model->getTitle());
        self::assertSame('Test Meta Title', $model->getMetaTitle());
        self::assertSame(str_repeat('A', 120), $model->getMetaDescription());
        self::assertSame(str_repeat('B', 50), $model->getExcerpt());
    }

    public function testSubmitMinimalData(): void
    {
        $model = new PostTranslation()->setLocale('en');
        $form = $this->factory->create(PostTranslationType::class, $model);

        $form->submit(['title' => 'Minimal Title']);

        self::assertTrue($form->isSynchronized());
        self::assertSame('en', $model->getLocale());
        self::assertSame('Minimal Title', $model->getTitle());
        self::assertSame('', $model->getMetaTitle());
        self::assertSame('', $model->getMetaDescription());
        self::assertSame('', $model->getExcerpt());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(PostTranslationType::class);

        self::assertTrue($form->has('locale'));
        self::assertTrue($form->has('title'));
        self::assertTrue($form->has('metaTitle'));
        self::assertTrue($form->has('slug'));
        self::assertTrue($form->has('metaDescription'));
        self::assertTrue($form->has('excerpt'));
    }

    public function testLocaleFieldChoicesMatchInjectedLocales(): void
    {
        $form = $this->factory->create(PostTranslationType::class);

        self::assertCount(2, $form->createView()['locale']->vars['choices']);
    }

    public function testSlugFieldIsDisabled(): void
    {
        $form = $this->factory->create(PostTranslationType::class);

        self::assertTrue($form->createView()['slug']->vars['disabled']);
    }

    public function testTitleFieldIsRequired(): void
    {
        $form = $this->factory->create(PostTranslationType::class);

        self::assertTrue($form->createView()['title']->vars['required']);
    }

    public function testOptionalFieldsAreNotRequired(): void
    {
        $form = $this->factory->create(PostTranslationType::class);

        $view = $form->createView();

        self::assertFalse($view['metaTitle']->vars['required']);
        self::assertFalse($view['metaDescription']->vars['required']);
        self::assertFalse($view['excerpt']->vars['required']);
    }

    public function testEmptyDataForTextFields(): void
    {
        $model = new PostTranslation();
        $form = $this->factory->create(PostTranslationType::class, $model);

        $form->submit([
            'locale' => 'fr',
            'title' => 'Test',
            'metaTitle' => null,
            'metaDescription' => null,
            'excerpt' => null,
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame('', $model->getMetaTitle());
        self::assertSame('', $model->getMetaDescription());
        self::assertSame('', $model->getExcerpt());
    }

    /**
     * @return list<FormExtensionInterface>
     */
    #[\Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([
                new PostTranslationType(new Locales('en|fr')),
            ], []),
        ];
    }
}
