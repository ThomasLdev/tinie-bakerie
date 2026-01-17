<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\PostSectionMedia;
use App\Entity\PostSectionMediaTranslation;
use App\Form\Type\PostSectionMediaTranslationType;
use App\Form\Type\PostSectionMediaType;
use JoliCode\MediaBundle\Bridge\EasyAdmin\Form\Type\MediaChoiceType;
use App\Services\Media\Enum\MediaType;
use JoliCode\MediaBundle\Library\LibraryContainer;
use JoliCode\MediaBundle\Resolver\Resolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

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
            'position' => 3,
            'media' => 'sections/test-video.mp4',
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
        self::assertSame('sections/test-video.mp4', $model->getMedia()?->getPath());
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
        self::assertTrue($form->has('media'));
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

    public function testMediaFieldIsNotRequired(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        $view = $form->createView();

        self::assertFalse($view['media']->vars['required']);
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
        $mediaChoiceType = new MediaChoiceType(
            $this->resolver,
            $this->libraryContainer,
        );

        return [
            new PreloadedExtension([$mediaChoiceType], []),
        ];
    }
}
