<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\CategoryMedia;
use App\Entity\CategoryMediaTranslation;
use App\Form\Type\CategoryMediaTranslationType;
use App\Form\Type\CategoryMediaType;
use App\Form\Type\TranslationsCollectionType;
use App\Services\Locale\Locales;
use JoliCode\MediaBundle\Bridge\EasyAdmin\Form\DataTransformer\MediaTransformer;
use JoliCode\MediaBundle\Bridge\EasyAdmin\Form\Type\MediaChoiceType;
use JoliCode\MediaBundle\Library\LibraryContainer;
use JoliCode\MediaBundle\Resolver\Resolver;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @internal
 */
#[CoversClass(CategoryMediaType::class)]
#[CoversClass(CategoryMediaTranslationType::class)]
#[CoversClass(TranslationsCollectionType::class)]
#[AllowMockObjectsWithoutExpectations]
final class CategoryMediaTypeTest extends TypeTestCase
{
    private MockObject&Resolver $resolver;

    private MockObject&LibraryContainer $libraryContainer;

    private MockObject&MediaTransformer $mediaTransformer;

    #[\Override]
    protected function setUp(): void
    {
        $this->resolver = $this->createMock(Resolver::class);
        $this->libraryContainer = $this->createMock(LibraryContainer::class);
        $this->mediaTransformer = $this->createMock(MediaTransformer::class);

        parent::setUp();
    }

    public function testSubmitValidDataWithTranslations(): void
    {
        $model = new CategoryMedia();
        $model->addTranslation(new CategoryMediaTranslation()->setLocale('fr'));
        $model->addTranslation(new CategoryMediaTranslation()->setLocale('en'));

        $form = $this->factory->create(CategoryMediaType::class, $model);

        $form->submit([
            'position' => 1,
            'translations' => [
                ['title' => 'Image Title FR', 'alt' => 'Image Alt Text'],
                ['title' => 'Image Title EN', 'alt' => 'Image Alt English'],
            ],
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame(1, $model->getPosition());
        self::assertCount(2, $model->getTranslations());

        $frTranslation = $model->getTranslations()->filter(
            static fn (CategoryMediaTranslation $t): bool => $t->getLocale() === 'fr',
        )->first();

        self::assertInstanceOf(CategoryMediaTranslation::class, $frTranslation);
        self::assertSame('Image Title FR', $frTranslation->getTitle());
        self::assertSame('Image Alt Text', $frTranslation->getAlt());
    }

    public function testTranslationsAreSeededOnNewModel(): void
    {
        $form = $this->factory->create(CategoryMediaType::class);

        self::assertCount(2, $form->createView()['translations']->children);
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(CategoryMediaType::class);

        self::assertTrue($form->has('position'));
        self::assertTrue($form->has('media'));
        self::assertTrue($form->has('translations'));
    }

    public function testPositionFieldIsRequired(): void
    {
        $form = $this->factory->create(CategoryMediaType::class);

        self::assertTrue($form->createView()['position']->vars['required']);
    }

    public function testMediaFieldIsNotRequired(): void
    {
        $form = $this->factory->create(CategoryMediaType::class);

        self::assertFalse($form->createView()['media']->vars['required']);
    }

    public function testTranslationsFieldIsRequired(): void
    {
        $form = $this->factory->create(CategoryMediaType::class);

        self::assertTrue($form->createView()['translations']->vars['required']);
    }

    public function testTranslationsCollectionLocksAddAndDelete(): void
    {
        $form = $this->factory->create(CategoryMediaType::class);

        $view = $form->createView();

        self::assertFalse($view['translations']->vars['allow_add']);
        self::assertFalse($view['translations']->vars['allow_delete']);
    }

    public function testTranslationsCollectionHasNoPrototype(): void
    {
        $form = $this->factory->create(CategoryMediaType::class);

        self::assertArrayNotHasKey('prototype', $form->createView()['translations']->vars);
    }

    /**
     * @return list<FormExtensionInterface>
     */
    #[\Override]
    protected function getExtensions(): array
    {
        $locales = new Locales('en|fr');

        $mediaChoiceType = new MediaChoiceType(
            $this->resolver,
            $this->libraryContainer,
            $this->mediaTransformer,
        );

        return [
            new PreloadedExtension([
                new CategoryMediaType(),
                new CategoryMediaTranslationType($locales),
                new TranslationsCollectionType($locales),
                $mediaChoiceType,
            ], []),
        ];
    }
}
