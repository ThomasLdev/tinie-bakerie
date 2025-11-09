<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\PostSectionMedia;
use App\Entity\PostSectionMediaTranslation;
use App\Form\PostSectionMediaTranslationType;
use App\Form\PostSectionMediaType;
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
 * Unit tests for PostSectionMediaType.
 * Tests form structure, embedded translations, and enum field configuration.
 *
 * @internal
 */
#[CoversClass(PostSectionMediaType::class)]
#[CoversClass(PostSectionMediaTranslationType::class)]
final class PostSectionMediaTypeTest extends TypeTestCase
{
    private MockObject&StorageInterface $storage;

    private MockObject&UploadHandler $handler;

    private MockObject&PropertyMappingFactory $mappingFactory;

    #[\Override]
    protected function setUp(): void
    {
        // Mock VichFileType dependencies
        $this->storage = $this->createMock(StorageInterface::class);
        $this->handler = $this->createMock(UploadHandler::class);
        $this->mappingFactory = $this->createMock(PropertyMappingFactory::class);

        parent::setUp();
    }

    public function testSubmitValidDataWithTranslations(): void
    {
        $formData = [
            'position' => 3,
            'type' => 'video',
            'translations' => [
                [
                    'locale' => 'fr',
                    'title' => 'Section Video Title FR',
                    'alt' => 'Section Video Alt',
                ],
                [
                    'locale' => 'en',
                    'title' => 'Section Video Title EN',
                    'alt' => 'Section Video Alt EN',
                ],
            ],
        ];

        $model = new PostSectionMedia();
        $form = $this->factory->create(PostSectionMediaType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame(3, $model->getPosition());
        self::assertSame(MediaType::Video, $model->getType());
        self::assertCount(2, $model->getTranslations());

        $frTranslation = $model->getTranslations()->filter(
            static fn (PostSectionMediaTranslation $t): bool => $t->getLocale() === 'fr',
        )->first();

        self::assertInstanceOf(PostSectionMediaTranslation::class, $frTranslation);
        self::assertSame('Section Video Title FR', $frTranslation->getTitle());
        self::assertSame('Section Video Alt', $frTranslation->getAlt());
    }

    public function testSubmitMinimalData(): void
    {
        $formData = [
            'position' => 1,
            'type' => 'image',
            'translations' => [
                [
                    'locale' => 'en',
                    'title' => '',
                    'alt' => '',
                ],
            ],
        ];

        $model = new PostSectionMedia();
        $form = $this->factory->create(PostSectionMediaType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame(1, $model->getPosition());
        self::assertSame(MediaType::Image, $model->getType());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('position'));
        self::assertTrue($form->has('mediaFile'));
        self::assertTrue($form->has('type'));
        self::assertTrue($form->has('translations'));
    }

    public function testPositionFieldIsRequired(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['position']->vars['required']);
    }

    public function testMediaFileFieldIsNotRequired(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertFalse($view['mediaFile']->vars['required']);
    }

    public function testTypeFieldIsRequired(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['type']->vars['required']);
    }

    public function testTypeFieldHasCorrectChoices(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class, null, [
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
        $form = $this->factory->create(PostSectionMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['translations']->vars['required']);
    }

    public function testTranslationsCollectionAllowsAddAndDelete(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['translations']->vars['allow_add']);
        self::assertTrue($view['translations']->vars['allow_delete']);
    }

    public function testTranslationsCollectionHasPrototype(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertArrayHasKey('prototype', $view['translations']->vars);
        self::assertNotNull($view['translations']->vars['prototype']);
    }

    #[\Override]
    protected function getExtensions(): array
    {
        // Create VichFileType with mocked dependencies
        $vichFileType = new VichFileType(
            $this->storage,
            $this->handler,
            $this->mappingFactory,
        );

        return [
            new PreloadedExtension([$vichFileType], []),
        ];
    }
}
