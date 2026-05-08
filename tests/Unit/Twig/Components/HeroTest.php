<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig\Components;

use App\Entity\Recipe;
use App\Entity\Tag;
use App\Entity\TagTranslation;
use App\Twig\Components\Hero;
use App\Twig\Extension\DurationExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
#[CoversClass(Hero::class)]
final class HeroTest extends TestCase
{
    private const LOCALE = 'en';

    #[TestDox('getTagTitles() returns an empty array when the recipe has no tags')]
    public function testReturnsEmptyArrayWhenNoTags(): void
    {
        $recipe = new Recipe();
        $component = $this->buildComponent($recipe);

        self::assertSame([], $component->getTagTitles());
    }

    #[TestDox('getTagTitles() returns every non-empty title in tag iteration order')]
    public function testReturnsTitlesInOrderForNonEmptyTags(): void
    {
        $recipe = new Recipe();
        $recipe->addTag($this->buildTag('Chocolate'));
        $recipe->addTag($this->buildTag('Dessert'));
        $recipe->addTag($this->buildTag('Quick'));

        $component = $this->buildComponent($recipe);

        self::assertSame(['Chocolate', 'Dessert', 'Quick'], $component->getTagTitles());
    }

    #[TestDox('getTagTitles() skips tags whose title resolves to an empty string while preserving order')]
    public function testSkipsTagsWithEmptyTitle(): void
    {
        $recipe = new Recipe();
        $recipe->addTag($this->buildTag('Chocolate'));
        // Tag with no translation set for the current locale -> getTitle() returns ''
        $recipe->addTag($this->buildTagWithoutTranslation());
        $recipe->addTag($this->buildTag('Quick'));

        $component = $this->buildComponent($recipe);

        self::assertSame(['Chocolate', 'Quick'], $component->getTagTitles());
    }

    #[TestDox('getTagTitles() keeps whitespace-only titles because the filter only excludes the empty string')]
    public function testKeepsWhitespaceOnlyTitles(): void
    {
        $recipe = new Recipe();
        $recipe->addTag($this->buildTag('Chocolate'));
        $recipe->addTag($this->buildTag('  '));
        $recipe->addTag($this->buildTag('Quick'));

        $component = $this->buildComponent($recipe);

        self::assertSame(['Chocolate', '  ', 'Quick'], $component->getTagTitles());
    }

    private function buildComponent(Recipe $recipe): Hero
    {
        $component = new Hero(
            $this->createStub(DurationExtension::class),
            $this->createStub(UrlGeneratorInterface::class),
        );
        $component->recipe = $recipe;

        return $component;
    }

    private function buildTag(string $title): Tag
    {
        $tag = new Tag();

        $translation = new TagTranslation();
        $translation->setLocale(self::LOCALE);
        $translation->setTitle($title);

        $tag->addTranslation($translation);
        $tag->setCurrentLocale(self::LOCALE);
        $tag->indexTranslations();

        return $tag;
    }

    private function buildTagWithoutTranslation(): Tag
    {
        $tag = new Tag();
        $tag->setCurrentLocale(self::LOCALE);
        $tag->indexTranslations();

        return $tag;
    }
}
