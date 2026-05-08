<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig\Components;

use App\Entity\RecipeStep as RecipeStepEntity;
use App\Services\Recipe\Enum\StepTipType;
use App\Twig\Components\RecipeStep;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RecipeStep::class)]
final class RecipeStepTest extends TestCase
{
    #[TestDox('getNumber() pads the index to at least two characters with leading zeros')]
    #[DataProvider('provideIndexes')]
    public function testGetNumberPadsIndex(int $index, string $expected): void
    {
        $component = new RecipeStep();
        $component->step = new RecipeStepEntity();
        $component->index = $index;

        self::assertSame($expected, $component->getNumber());
    }

    public static function provideIndexes(): \Generator
    {
        yield 'zero' => [0, '00'];
        yield 'single digit one' => [1, '01'];
        yield 'single digit nine' => [9, '09'];
        yield 'two digits ten' => [10, '10'];
        yield 'two digits ninety-nine' => [99, '99'];
        yield 'three digits one hundred (no truncation)' => [100, '100'];
        yield 'three digits nine hundred ninety-nine' => [999, '999'];
    }

    #[TestDox('getTipIcon() returns the warning icon when tip type is Warning')]
    public function testGetTipIconReturnsWarningIcon(): void
    {
        $component = $this->buildComponent(StepTipType::Warning);

        self::assertSame('lucide:triangle-alert', $component->getTipIcon());
    }

    #[TestDox('getTipIcon() falls back to the lightbulb icon for non-Warning tip types')]
    #[DataProvider('provideNonWarningTipTypes')]
    public function testGetTipIconFallsBackToLightbulb(?StepTipType $tipType): void
    {
        $component = $this->buildComponent($tipType);

        self::assertSame('lucide:lightbulb', $component->getTipIcon());
    }

    #[TestDox('getTipLabelKey() returns the warning translation key when tip type is Warning')]
    public function testGetTipLabelKeyReturnsWarningKey(): void
    {
        $component = $this->buildComponent(StepTipType::Warning);

        self::assertSame('recipe.show.steps.warning', $component->getTipLabelKey());
    }

    #[TestDox('getTipLabelKey() falls back to the tip translation key for non-Warning tip types')]
    #[DataProvider('provideNonWarningTipTypes')]
    public function testGetTipLabelKeyFallsBackToTipKey(?StepTipType $tipType): void
    {
        $component = $this->buildComponent($tipType);

        self::assertSame('recipe.show.steps.tip', $component->getTipLabelKey());
    }

    public static function provideNonWarningTipTypes(): \Generator
    {
        yield 'Tip case' => [StepTipType::Tip];
        yield 'null (no tip type set)' => [null];
    }

    private function buildComponent(?StepTipType $tipType): RecipeStep
    {
        $entity = new RecipeStepEntity();
        $entity->setTipType($tipType);

        $component = new RecipeStep();
        $component->step = $entity;
        $component->index = 1;

        return $component;
    }
}
