<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\PostMedia;
use App\Entity\PostMediaTranslation;
use App\Form\Type\PostMediaTranslationType;
use App\Form\Type\PostMediaType;
use JoliCode\MediaBundle\Bridge\EasyAdmin\Form\DataTransformer\MediaTransformer;
use JoliCode\MediaBundle\Bridge\EasyAdmin\Form\Type\MediaChoiceType;
use JoliCode\MediaBundle\Library\LibraryContainer;
use JoliCode\MediaBundle\Resolver\Resolver;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Unit tests for PostMediaType.
 * Tests form structure and embedded translations.
 *
 * @internal
 */
#[CoversClass(PostMediaType::class)]
#[CoversClass(PostMediaTranslationType::class)]
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
        $formData = [
            'position' => 2,
            'translations' => [
                [
                    'locale' => 'fr',
                    'title' => 'Post Image Title FR',
                    'alt' => 'Post Image Alt',
                ],
                [
                    'locale' => 'en',
                    'title' => 'Post Image Title EN',
                    'alt' => 'Post Image Alt EN',
                ],
            ],
        ];

        $model = new PostMedia();
        $form = $this->factory->create(PostMediaType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

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

    public function testSubmitMinimalDataWithEmptyDataForPosition(): void
    {
        $formData = [
            'position' => '',
            'translations' => [
                [
                    'locale' => 'en',
                    'title' => '',
                    'alt' => '',
                ],
            ],
        ];

        $model = new PostMedia();
        $form = $this->factory->create(PostMediaType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame(0, $model->getPosition());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(PostMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('position'));
        self::assertTrue($form->has('media'));
        self::assertTrue($form->has('translations'));
    }

    public function testPositionFieldIsRequired(): void
    {
        $form = $this->factory->create(PostMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['position']->vars['required']);
    }

    public function testPositionFieldHasMinAttribute(): void
    {
        $form = $this->factory->create(PostMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertArrayHasKey('min', $view['position']->vars['attr']);
        self::assertSame(0, $view['position']->vars['attr']['min']);
    }

    public function testMediaFieldIsNotRequired(): void
    {
        $form = $this->factory->create(PostMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertFalse($view['media']->vars['required']);
    }

    public function testTranslationsFieldIsRequired(): void
    {
        $form = $this->factory->create(PostMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['translations']->vars['required']);
    }

    public function testTranslationsCollectionAllowsAddAndDelete(): void
    {
        $form = $this->factory->create(PostMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['translations']->vars['allow_add']);
        self::assertTrue($view['translations']->vars['allow_delete']);
    }

    public function testTranslationsCollectionHasPrototype(): void
    {
        $form = $this->factory->create(PostMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertArrayHasKey('prototype', $view['translations']->vars);
        self::assertNotNull($view['translations']->vars['prototype']);
    }

    #[\Override]
    protected function getExtensions(): array
    {
        $mediaChoiceType = new MediaChoiceType(
            $this->resolver,
            $this->libraryContainer,
            $this->mediaTransformer,
        );

        return [
            new PreloadedExtension([$mediaChoiceType], []),
        ];
    }
}
