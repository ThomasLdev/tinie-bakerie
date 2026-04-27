<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\PostSectionMedia;
use App\Entity\PostSectionMediaTranslation;
use App\Form\Type\PostSectionMediaTranslationType;
use App\Form\Type\PostSectionMediaType;
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
#[CoversClass(PostSectionMediaType::class)]
#[CoversClass(PostSectionMediaTranslationType::class)]
#[CoversClass(TranslationsCollectionType::class)]
#[AllowMockObjectsWithoutExpectations]
final class PostSectionMediaTypeTest extends TypeTestCase
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
        $model = new PostSectionMedia();
        $model->addTranslation(new PostSectionMediaTranslation()->setLocale('fr'));
        $model->addTranslation(new PostSectionMediaTranslation()->setLocale('en'));

        $form = $this->factory->create(PostSectionMediaType::class, $model);

        $form->submit([
            'position' => 3,
            'translations' => [
                ['title' => 'Section Video Title FR', 'alt' => 'Section Video Alt'],
                ['title' => 'Section Video Title EN', 'alt' => 'Section Video Alt EN'],
            ],
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame(3, $model->getPosition());
        self::assertCount(2, $model->getTranslations());

        $frTranslation = $model->getTranslations()->filter(
            static fn (PostSectionMediaTranslation $t): bool => $t->getLocale() === 'fr',
        )->first();

        self::assertInstanceOf(PostSectionMediaTranslation::class, $frTranslation);
        self::assertSame('Section Video Title FR', $frTranslation->getTitle());
        self::assertSame('Section Video Alt', $frTranslation->getAlt());
    }

    public function testTranslationsAreSeededOnNewModel(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class);

        self::assertCount(2, $form->createView()['translations']->children);
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class);

        self::assertTrue($form->has('position'));
        self::assertTrue($form->has('media'));
        self::assertTrue($form->has('translations'));
    }

    public function testPositionFieldIsRequired(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class);

        self::assertTrue($form->createView()['position']->vars['required']);
    }

    public function testMediaFieldIsNotRequired(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class);

        self::assertFalse($form->createView()['media']->vars['required']);
    }

    public function testTranslationsFieldIsRequired(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class);

        self::assertTrue($form->createView()['translations']->vars['required']);
    }

    public function testTranslationsCollectionLocksAddAndDelete(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class);

        $view = $form->createView();

        self::assertFalse($view['translations']->vars['allow_add']);
        self::assertFalse($view['translations']->vars['allow_delete']);
    }

    public function testTranslationsCollectionHasNoPrototype(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class);

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
                new PostSectionMediaType(),
                new PostSectionMediaTranslationType($locales),
                new TranslationsCollectionType($locales),
                $mediaChoiceType,
            ], []),
        ];
    }
}
