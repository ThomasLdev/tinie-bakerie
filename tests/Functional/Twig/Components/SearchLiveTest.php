<?php

declare(strict_types=1);

namespace App\Tests\Functional\Twig\Components;

use App\Twig\Components\Search;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

/**
 * @internal
 */
#[CoversClass(Search::class)]
final class SearchLiveTest extends KernelTestCase
{
    use InteractsWithLiveComponents;

    #[TestDox('fillQuery LiveAction sets the query prop through the framework')]
    public function testFillQueryActionSetsQuery(): void
    {
        $component = $this->createLiveComponent('Search');

        $component->call('fillQuery', ['value' => 'chocolat']);

        $instance = $component->component();
        self::assertInstanceOf(Search::class, $instance);
        self::assertSame('chocolat', $instance->query);
    }

    #[TestDox('clear LiveAction resets the query prop to an empty string')]
    public function testClearActionResetsQuery(): void
    {
        $component = $this->createLiveComponent('Search');

        $component->set('query', 'chocolat');

        $beforeClear = $component->component();
        self::assertInstanceOf(Search::class, $beforeClear);
        self::assertSame('chocolat', $beforeClear->query);

        $component->call('clear');

        $afterClear = $component->component();
        self::assertInstanceOf(Search::class, $afterClear);
        self::assertSame('', $afterClear->query);
    }

    #[TestDox('getSuggestions returns the 7 hardcoded entries in order')]
    public function testGetSuggestionsReturnsSevenEntries(): void
    {
        $component = $this->createLiveComponent('Search');

        $instance = $component->component();
        self::assertInstanceOf(Search::class, $instance);

        self::assertSame(
            [
                'Tarte tatin',
                'Chocolat',
                'Citron',
                'Vanille',
                'Rhubarbe',
                'Caramel',
                'Sans gluten',
            ],
            $instance->getSuggestions(),
        );
    }

    #[TestDox('isEmpty is true when query is shorter than 2 characters')]
    public function testIsEmptyForShortQuery(): void
    {
        $component = $this->createLiveComponent('Search');

        $component->set('query', 'a');

        $instance = $component->component();
        self::assertInstanceOf(Search::class, $instance);
        self::assertTrue($instance->isEmpty());
    }

    #[TestDox('isEmpty is false when query has 2 or more characters')]
    public function testIsEmptyForValidQuery(): void
    {
        $component = $this->createLiveComponent('Search');

        $component->set('query', 'ab');

        $instance = $component->component();
        self::assertInstanceOf(Search::class, $instance);
        self::assertFalse($instance->isEmpty());
    }
}
