<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Type;

use App\Entity\PostSectionMedia;
use App\Entity\PostSectionMediaTranslation;
use App\Form\Type\PostSectionMediaTranslationType;
use App\Form\Type\PostSectionMediaType;
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
 * Unit tests for PostSectionMediaType.
 * Tests form structure and embedded translations.
 *
 * @internal
 */
#[CoversClass(PostSectionMediaType::class)]
#[CoversClass(PostSectionMediaTranslationType::class)]
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
        $formData = [
            'position' => 3,
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
    }

    public function testFormHasCorrectFields(): void
    {
        $form = $this->factory->create(PostSectionMediaType::class, null, [
            'supported_locales' => ['en', 'fr'],
        ]);

        self::assertTrue($form->has('position'));
        self::assertTrue($form->has('media'));
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
            $this->mediaTransformer,
        );

        return [
            new PreloadedExtension([$mediaChoiceType], []),
        ];
    }
}
