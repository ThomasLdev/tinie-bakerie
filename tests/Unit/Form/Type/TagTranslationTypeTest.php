<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\TagTranslation;
use App\Form\Type\TagTranslationType;
use App\Services\Locale\Locales;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class TagTranslationTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $model = new TagTranslation()->setLocale('fr');
        $form = $this->factory->create(TagTranslationType::class, $model);

        $form->submit(['title' => 'Test Tag Title']);

        self::assertTrue($form->isSynchronized());
        self::assertSame('fr', $model->getLocale());
        self::assertSame('Test Tag Title', $model->getTitle());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(TagTranslationType::class);

        self::assertTrue($form->has('locale'));
        self::assertTrue($form->has('title'));
        self::assertFalse($form->has('slug'));
    }

    public function testLocaleFieldChoicesMatchInjectedLocales(): void
    {
        $form = $this->factory->create(TagTranslationType::class);

        $view = $form->createView();

        self::assertCount(2, $view['locale']->vars['choices']);
    }

    public function testLocaleFieldIsRequired(): void
    {
        $form = $this->factory->create(TagTranslationType::class);

        self::assertTrue($form->createView()['locale']->vars['required']);
    }

    public function testTitleFieldIsRequired(): void
    {
        $form = $this->factory->create(TagTranslationType::class);

        self::assertTrue($form->createView()['title']->vars['required']);
    }

    /**
     * @return list<FormExtensionInterface>
     */
    #[\Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([
                new TagTranslationType(new Locales('en|fr')),
            ], []),
        ];
    }
}
