<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\CategoryMedia;
use App\Entity\CategoryMediaTranslation;
use App\Form\Type\CategoryMediaTranslationType;
use App\Form\Type\CategoryMediaType;
use JoliCode\MediaBundle\Bridge\EasyAdmin\Form\Type\MediaChoiceType;
use App\Services\Media\Enum\MediaType;
use JoliCode\MediaBundle\Library\LibraryContainer;
use JoliCode\MediaBundle\Resolver\Resolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Unit tests for CategoryMediaType.
 * Tests form structure, embedded translations, and enum field configuration.
 *
 * @internal
 */
#[CoversClass(CategoryMediaType::class)]
#[CoversClass(CategoryMediaTranslationType::class)]
final class CategoryMediaTypeTest extends TypeTestCase
{
    private MockObject&Resolver $resolver;
    private MockObject&LibraryContainer $libraryContainer;

    #[\Override]
    protected function setUp(): void
    {
        $this->resolver = $this->createMock(Resolver::class);
        $this->libraryContainer = $this->createMock(LibraryContainer::class);

        parent::setUp();
    }

    public function testSubmitValidDataWithTranslations(): void
    {
        $formData = [
            'position' => 1,
            'media' => 'categories/test-image.jpg',
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
        self::assertSame('categories/test-image.jpg', $model->getMedia()?->getPath());
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
        self::assertTrue($form->has('type'));
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

    public function testTypeFieldIsRequired(): void
    {
        $form = $this->factory->create(CategoryMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['type']->vars['required']);
    }

    public function testTypeFieldHasCorrectChoices(): void
    {
        $form = $this->factory->create(CategoryMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();
        $choices = $view['type']->vars['choices'];

        self::assertCount(2, $choices);

        // The choices array keys contain the enum name (Image, Video) from array_combine
        $hasImage = false;
        $hasVideo = false;

        foreach ($choices as $choiceView) {
            if ($choiceView->label === 'Image') {
                $hasImage = true;
            }

            if ($choiceView->label === 'Video') {
                $hasVideo = true;
            }
        }

        self::assertTrue($hasImage, 'Image choice not found');
        self::assertTrue($hasVideo, 'Video choice not found');
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
        );

        return [
            new PreloadedExtension([$mediaChoiceType], []),
        ];
    }
}
