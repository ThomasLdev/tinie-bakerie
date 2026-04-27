<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig\Extension;

use App\Twig\Extension\DurationExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;

/**
 * @internal
 */
#[CoversClass(DurationExtension::class)]
final class DurationExtensionTest extends TestCase
{
    private DurationExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new DurationExtension();
    }

    #[Test]
    public function itExposesADurationFilter(): void
    {
        $filters = $this->extension->getFilters();

        self::assertCount(1, $filters);
        self::assertInstanceOf(TwigFilter::class, $filters[0]);
        self::assertSame('duration', $filters[0]->getName());
    }

    /**
     * @return iterable<string, array{0: int|null, 1: string|null}>
     */
    public static function provideNullCases(): iterable
    {
        yield 'returns null when minutes is null' => [null, null];
        yield 'returns null when minutes is zero' => [0, null];
        yield 'returns null when minutes is negative' => [-1, null];
        yield 'returns null when minutes is deeply negative' => [-1000, null];
    }

    /**
     * @return iterable<string, array{0: int, 1: string}>
     */
    public static function provideMinutesOnly(): iterable
    {
        yield 'formats one minute' => [1, '1 min'];
        yield 'formats typical short duration' => [25, '25 min'];
        yield 'formats just under an hour' => [59, '59 min'];
    }

    /**
     * @return iterable<string, array{0: int, 1: string}>
     */
    public static function provideExactHours(): iterable
    {
        yield 'formats exactly one hour' => [60, '1 h'];
        yield 'formats exactly two hours' => [120, '2 h'];
        yield 'formats exactly twenty-four hours' => [1440, '24 h'];
    }

    /**
     * @return iterable<string, array{0: int, 1: string}>
     */
    public static function provideHoursWithRemainder(): iterable
    {
        yield 'formats one hour and one minute' => [61, '1 h 1'];
        yield 'formats one hour and ten minutes' => [70, '1 h 10'];
        yield 'formats one hour and fifty-nine minutes' => [119, '1 h 59'];
        yield 'formats two hours and five minutes' => [125, '2 h 5'];
        yield 'formats long duration over a day' => [1500, '25 h'];
        yield 'formats long duration with remainder' => [1505, '25 h 5'];
    }

    #[Test]
    #[DataProvider('provideNullCases')]
    #[DataProvider('provideMinutesOnly')]
    #[DataProvider('provideExactHours')]
    #[DataProvider('provideHoursWithRemainder')]
    public function itFormatsMinutesAsHumanReadableDuration(?int $minutes, ?string $expected): void
    {
        self::assertSame($expected, $this->extension->formatDuration($minutes));
    }
}
