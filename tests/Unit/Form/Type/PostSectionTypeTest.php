<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\PostSection;
use App\Entity\PostSectionMedia;
use App\Entity\PostSectionTranslation;
use App\Form\Type\PostSectionTranslationType;
use App\Form\Type\PostSectionType;
use App\Services\PostSection\Enum\PostSectionType as PostSectionTypeEnum;
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

    public function testSubmitValidDataWithMediaAndTranslations(): void
    {
        // Translations cardinality is fixed = supported locales. Pre-populate the
        // model. The nested media collection is dynamic (allow_add) so a new media
        // is created via empty_data with its own pre-bound translations.
        $formData = [
            'position' => 1,
            'type' => 'two_columns',
            'media' => [
                [
                    'position' => 0,
                    'translations' => [
                        ['title' => 'Media Title FR', 'alt' => 'Media Alt FR'],
                        ['title' => '', 'alt' => ''],
                    ],
                ],
            ],
            'translations' => [
                ['title' => 'Section Title FR', 'content' => 'Section Content FR'],
                ['title' => 'Section Title EN', 'content' => 'Section Content EN'],
            ],
        ];

        $model = new PostSection();
        $model->addTranslation(new PostSectionTranslation()->setLocale('fr'));
        $model->addTranslation(new PostSectionTranslation()->setLocale('en'));

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
    }

    public function testSubmitMinimalDataWithoutMedia(): void
    {
        $formData = [
            'position' => 0,
            'type' => 'default',
            'media' => [],
            'translations' => [
                ['title' => 'Minimal Title', 'content' => ''],
                ['title' => '', 'content' => ''],
            ],
        ];

        $model = new PostSection();
        $model->addTranslation(new PostSectionTranslation()->setLocale('fr'));
        $model->addTranslation(new PostSectionTranslation()->setLocale('en'));

        $form = $this->factory->create(PostSectionType::class, $model, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame(0, $model->getPosition());
        self::assertSame(PostSectionTypeEnum::Default, $model->getType());
        self::assertCount(0, $model->getMedia());
        self::assertCount(2, $model->getTranslations());
    }

    public function testPositionHasEmptyData(): void
    {
        $formData = [
            'position' => '',
            'type' => 'default',
            'translations' => [
                ['title' => 'Test', 'content' => ''],
                ['title' => '', 'content' => ''],
            ],
        ];

        $model = new PostSection();
        $model->addTranslation(new PostSectionTranslation()->setLocale('fr'));
        $model->addTranslation(new PostSectionTranslation()->setLocale('en'));

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

        // Labels are translation keys (resolved at render time by the translator).
        $expectedLabels = [
            'admin.post_section.layout.default' => false,
            'admin.post_section.layout.two_columns' => false,
            'admin.post_section.layout.two_columns_media_left' => false,
        ];

        foreach ($choices as $choiceView) {
            if (\array_key_exists($choiceView->label, $expectedLabels)) {
                $expectedLabels[$choiceView->label] = true;
            }
        }

        foreach ($expectedLabels as $label => $found) {
            self::assertTrue($found, \sprintf('Choice "%s" not found', $label));
        }
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

    public function testTranslationsCollectionLocksAddAndDelete(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertFalse($view['translations']->vars['allow_add']);
        self::assertFalse($view['translations']->vars['allow_delete']);
    }

    public function testTranslationsCollectionHasNoPrototype(): void
    {
        $form = $this->factory->create(PostSectionType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertArrayNotHasKey('prototype', $view['translations']->vars);
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
