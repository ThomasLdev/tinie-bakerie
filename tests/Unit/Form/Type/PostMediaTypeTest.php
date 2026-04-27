<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\PostMedia;
use App\Entity\PostMediaTranslation;
use App\Form\Type\PostMediaTranslationType;
use App\Form\Type\PostMediaType;
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
#[CoversClass(PostMediaType::class)]
#[CoversClass(PostMediaTranslationType::class)]
#[CoversClass(TranslationsCollectionType::class)]
#[AllowMockObjectsWithoutExpectations]
final class PostMediaTypeTest extends TypeTestCase
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
        $model = new PostMedia();
        $model->addTranslation(new PostMediaTranslation()->setLocale('fr'));
        $model->addTranslation(new PostMediaTranslation()->setLocale('en'));

        $form = $this->factory->create(PostMediaType::class, $model);

        $form->submit([
            'position' => 2,
            'translations' => [
                ['title' => 'Post Image Title FR', 'alt' => 'Post Image Alt'],
                ['title' => 'Post Image Title EN', 'alt' => 'Post Image Alt EN'],
            ],
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame(2, $model->getPosition());
        self::assertCount(2, $model->getTranslations());

        $enTranslation = $model->getTranslations()->filter(
            static fn (PostMediaTranslation $t): bool => $t->getLocale() === 'en',
        )->first();

        self::assertInstanceOf(PostMediaTranslation::class, $enTranslation);
        self::assertSame('Post Image Title EN', $enTranslation->getTitle());
        self::assertSame('Post Image Alt EN', $enTranslation->getAlt());
    }

    public function testTranslationsAreSeededOnNewModel(): void
    {
        $form = $this->factory->create(PostMediaType::class);

        // The model attached to the form is the seeded PostMedia (with N
        // translations) — the inner CollectionType has rendered N entries.
        $view = $form->createView();

        self::assertCount(2, $view['translations']->children);
    }

    public function testSubmitMinimalDataWithEmptyDataForPosition(): void
    {
        $form = $this->factory->create(PostMediaType::class, new PostMedia());

        $form->submit([
            'position' => '',
            'translations' => [
                ['title' => '', 'alt' => ''],
                ['title' => '', 'alt' => ''],
            ],
        ]);

        self::assertTrue($form->isSynchronized());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(PostMediaType::class);

        self::assertTrue($form->has('position'));
        self::assertTrue($form->has('media'));
        self::assertTrue($form->has('translations'));
    }

    public function testPositionFieldIsRequired(): void
    {
        $form = $this->factory->create(PostMediaType::class);

        $view = $form->createView();

        self::assertTrue($view['position']->vars['required']);
    }

    public function testPositionFieldHasMinAttribute(): void
    {
        $form = $this->factory->create(PostMediaType::class);

        $view = $form->createView();

        self::assertArrayHasKey('min', $view['position']->vars['attr']);
        self::assertSame(0, $view['position']->vars['attr']['min']);
    }

    public function testMediaFieldIsNotRequired(): void
    {
        $form = $this->factory->create(PostMediaType::class);

        self::assertFalse($form->createView()['media']->vars['required']);
    }

    public function testTranslationsFieldIsRequired(): void
    {
        $form = $this->factory->create(PostMediaType::class);

        self::assertTrue($form->createView()['translations']->vars['required']);
    }

    public function testTranslationsCollectionLocksAddAndDelete(): void
    {
        $form = $this->factory->create(PostMediaType::class);

        $view = $form->createView();

        self::assertFalse($view['translations']->vars['allow_add']);
        self::assertFalse($view['translations']->vars['allow_delete']);
    }

    public function testTranslationsCollectionHasNoPrototype(): void
    {
        $form = $this->factory->create(PostMediaType::class);

        $view = $form->createView();

        self::assertArrayNotHasKey('prototype', $view['translations']->vars);
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
                new PostMediaType(),
                new PostMediaTranslationType($locales),
                new TranslationsCollectionType($locales),
                $mediaChoiceType,
            ], []),
        ];
    }
}
