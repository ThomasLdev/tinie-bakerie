<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\PostSection;
use App\Entity\PostSectionMedia;
use App\Entity\PostSectionTranslation;
use App\Form\PostSectionTranslationType;
use App\Form\PostSectionType;
use App\Services\Media\Enum\MediaType;
use App\Services\PostSection\Enum\PostSectionType as PostSectionTypeEnum;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Unit tests for PostSectionType.
 * Tests form structure with multiple collections (media + translations).
 *
 * @internal
 */
#[CoversClass(PostSectionType::class)]
#[CoversClass(PostSectionTranslationType::class)]
#[AllowMockObjectsWithoutExpectations]
final class PostSectionTypeTest extends TypeTestCase
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

    public function testSubmitValidDataWithMediaAndTranslations(): void
    {
        $formData = [
            'position' => 1,
            'type' => 'two_columns',
            'media' => [
                [
                    'position' => 0,
                    'type' => 'image',
                    'translations' => [
                        [
                            'locale' => 'fr',
                            'title' => 'Media Title FR',
                            'alt' => 'Media Alt FR',
                        ],
                    ],
                ],
            ],
            'translations' => [
                [
                    'locale' => 'fr',
                    'title' => 'Section Title FR',
                    'content' => 'Section Content FR',
                ],
                [
                    'locale' => 'en',
                    'title' => 'Section Title EN',
                    'content' => 'Section Content EN',
                ],
            ],
        ];

        $model = new PostSection();
        $form = $this->factory->create(PostSectionType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame(1, $model->getPosition());
        self::assertSame(PostSectionTypeEnum::TwoColumns, $model->getType());

        // Check translations
        self::assertCount(2, $model->getTranslations());
        $frTranslation = $model->getTranslations()->filter(
            static fn (PostSectionTranslation $t): bool => $t->getLocale() === 'fr',
        )->first();

        self::assertInstanceOf(PostSectionTranslation::class, $frTranslation);
        self::assertSame('Section Title FR', $frTranslation->getTitle());
        self::assertSame('Section Content FR', $frTranslation->getContent());

        // Check media collection
        self::assertCount(1, $model->getMedia());
        $media = $model->getMedia()->first();
        self::assertInstanceOf(PostSectionMedia::class, $media);
        self::assertSame(0, $media->getPosition());
        self::assertSame(MediaType::Image, $media->getType());
    }

    public function testSubmitMinimalDataWithoutMedia(): void
    {
        $formData = [
            'position' => 0,
            'type' => 'default',
            'media' => [],
            'translations' => [
                [
                    'locale' => 'en',
                    'title' => 'Minimal Title',
                    'content' => '',
                ],
            ],
        ];

        $model = new PostSection();
        $form = $this->factory->create(PostSectionType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame(0, $model->getPosition());
        self::assertSame(PostSectionTypeEnum::Default, $model->getType());
        self::assertCount(0, $model->getMedia());
        self::assertCount(1, $model->getTranslations());
    }

    public function testPositionHasEmptyData(): void
    {
        $formData = [
            'position' => '',
            'type' => 'default',
            'translations' => [
                [
                    'locale' => 'en',
                    'title' => 'Test',
                    'content' => '',
                ],
            ],
        ];

        $model = new PostSection();
        $form = $this->factory->create(PostSectionType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame(0, $model->getPosition());
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('position'));
        self::assertTrue($form->has('type'));
        self::assertTrue($form->has('media'));
        self::assertTrue($form->has('translations'));
    }

    public function testPositionFieldIsRequired(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['position']->vars['required']);
    }

    public function testPositionFieldHasMinAttributeInAttrs(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertArrayHasKey('min', $view['position']->vars['attr']);
        self::assertSame(0, $view['position']->vars['attr']['min']);
        self::assertArrayHasKey('class', $view['position']->vars['attr']);
        self::assertSame('form-control', $view['position']->vars['attr']['class']);
    }

    public function testTypeFieldIsRequired(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['type']->vars['required']);
    }

    public function testTypeFieldHasCorrectChoices(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();
        $choices = $view['type']->vars['choices'];

        self::assertCount(3, $choices);

        // Check that the enum labels are present
        $hasDefault = false;
        $hasTwoColumns = false;
        $hasTwoColumnsMediaLeft = false;

        foreach ($choices as $choiceView) {
            if ($choiceView->label === 'Default') {
                $hasDefault = true;
            }

            if ($choiceView->label === 'Two Columns') {
                $hasTwoColumns = true;
            }

            if ($choiceView->label === 'Two Columns Media Left') {
                $hasTwoColumnsMediaLeft = true;
            }
        }

        self::assertTrue($hasDefault, 'Default choice not found');
        self::assertTrue($hasTwoColumns, 'Two Columns choice not found');
        self::assertTrue($hasTwoColumnsMediaLeft, 'Two Columns Media Left choice not found');
    }

    public function testMediaFieldIsNotRequired(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertFalse($view['media']->vars['required']);
    }

    public function testMediaCollectionAllowsAddAndDelete(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['media']->vars['allow_add']);
        self::assertTrue($view['media']->vars['allow_delete']);
    }

    public function testMediaCollectionHasPrototype(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertArrayHasKey('prototype', $view['media']->vars);
        self::assertNotNull($view['media']->vars['prototype']);
    }

    public function testTranslationsFieldIsRequired(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['translations']->vars['required']);
    }

    public function testTranslationsCollectionAllowsAddAndDelete(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertTrue($view['translations']->vars['allow_add']);
        self::assertTrue($view['translations']->vars['allow_delete']);
    }

    public function testTranslationsCollectionHasPrototype(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
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
