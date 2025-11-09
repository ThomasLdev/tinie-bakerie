<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\PostMedia;
use App\Entity\PostMediaTranslation;
use App\Form\PostMediaTranslationType;
use App\Form\PostMediaType;
use App\Services\Media\Enum\MediaType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Unit tests for PostMediaType.
 * Tests form structure, embedded translations, and enum field configuration.
 *
 * @internal
 */
#[CoversClass(PostMediaType::class)]
#[CoversClass(PostMediaTranslationType::class)]
final class PostMediaTypeTest extends TypeTestCase
{
    private MockObject&StorageInterface $storage;
    private MockObject&UploadHandler $handler;
    private MockObject&PropertyMappingFactory $mappingFactory;

    protected function setUp(): void
    {
        // Mock VichFileType dependencies
        $this->storage = $this->createMock(StorageInterface::class);
        $this->handler = $this->createMock(UploadHandler::class);
        $this->mappingFactory = $this->createMock(PropertyMappingFactory::class);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        // Create VichFileType with mocked dependencies
        $vichFileType = new VichFileType(
            $this->storage,
            $this->handler,
            $this->mappingFactory
        );

        return [
            new PreloadedExtension([$vichFileType], []),
        ];
    }

    public function testSubmitValidDataWithTranslations(): void
    {
        $formData = [
            'position' => 2,
            'type' => 'image',
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
        self::assertSame(MediaType::Image, $model->getType());
        self::assertCount(2, $model->getTranslations());

        $enTranslation = $model->getTranslations()->filter(
            static fn (PostMediaTranslation $t) => $t->getLocale() === 'en'
        )->first();

        self::assertInstanceOf(PostMediaTranslation::class, $enTranslation);
        self::assertSame('Post Image Title EN', $enTranslation->getTitle());
        self::assertSame('Post Image Alt EN', $enTranslation->getAlt());
    }

    public function testSubmitMinimalDataWithEmptyDataForPosition(): void
    {
        $formData = [
            'position' => '',
            'type' => 'video',
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
        self::assertSame(MediaType::Video, $model->getType());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(PostMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('position'));
        self::assertTrue($form->has('mediaFile'));
        self::assertTrue($form->has('type'));
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

    public function testMediaFileFieldIsNotRequired(): void
    {
        $form = $this->factory->create(PostMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertFalse($view['mediaFile']->vars['required']);
    }

    public function testTypeFieldIsRequired(): void
    {
        $form = $this->factory->create(PostMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['type']->vars['required']);
    }

    public function testTypeFieldHasCorrectChoices(): void
    {
        $form = $this->factory->create(PostMediaType::class, null, [
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
}
