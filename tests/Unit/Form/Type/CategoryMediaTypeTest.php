<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\CategoryMedia;
use App\Entity\CategoryMediaTranslation;
use App\Form\Type\CategoryMediaTranslationType;
use App\Form\Type\CategoryMediaType;
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
 * Unit tests for CategoryMediaType.
 * Tests form structure and embedded translations.
 *
 * @internal
 */
#[CoversClass(CategoryMediaType::class)]
#[CoversClass(CategoryMediaTranslationType::class)]
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
        $formData = [
            'position' => 1,
            'translations' => [
                [
                    'locale' => 'fr',
                    'title' => 'Image Title FR',
                    'alt' => 'Image Alt Text',
                ],
                [
                    'locale' => 'en',
                    'title' => 'Image Title EN',
                    'alt' => 'Image Alt English',
                ],
            ],
        ];

        $model = new CategoryMedia();
        $form = $this->factory->create(CategoryMediaType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid(), 'Form has errors: ' . $form->getErrors(true));
        self::assertSame(1, $model->getPosition());
        self::assertCount(2, $model->getTranslations());

        $frTranslation = $model->getTranslations()->filter(
            static fn (CategoryMediaTranslation $t): bool => $t->getLocale() === 'fr',
        )->first();

        self::assertInstanceOf(CategoryMediaTranslation::class, $frTranslation);
        self::assertSame('Image Title FR', $frTranslation->getTitle());
        self::assertSame('Image Alt Text', $frTranslation->getAlt());
    }

    public function testSubmitMinimalData(): void
    {
        $formData = [
            'position' => 0,
            'translations' => [
                [
                    'locale' => 'en',
                    'title' => '',
                    'alt' => '',
                ],
            ],
        ];

        $model = new CategoryMedia();
        $form = $this->factory->create(CategoryMediaType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame(0, $model->getPosition());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(CategoryMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('position'));
        self::assertTrue($form->has('media'));
        self::assertTrue($form->has('translations'));
    }

    public function testPositionFieldIsRequired(): void
    {
        $form = $this->factory->create(CategoryMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['position']->vars['required']);
    }

    public function testMediaFieldIsNotRequired(): void
    {
        $form = $this->factory->create(CategoryMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertFalse($view['media']->vars['required']);
    }

    public function testTranslationsFieldIsRequired(): void
    {
        $form = $this->factory->create(CategoryMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['translations']->vars['required']);
    }

    public function testTranslationsCollectionAllowsAddAndDelete(): void
    {
        $form = $this->factory->create(CategoryMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['translations']->vars['allow_add']);
        self::assertTrue($view['translations']->vars['allow_delete']);
    }

    public function testTranslationsCollectionHasPrototype(): void
    {
        $form = $this->factory->create(CategoryMediaType::class, null, [
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
