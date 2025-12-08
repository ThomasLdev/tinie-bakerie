<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services\Locale;

use App\Services\Locale\LocaleProvider;
use App\Services\Locale\Locales;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[CoversClass(LocaleProvider::class)]
#[CoversClass(Locales::class)]
final class LocaleProviderTest extends KernelTestCase
{
    public function testReturnsDefaultLocaleWhenNoRequest(): void
    {
        self::bootKernel();

        $localeProvider = self::getContainer()->get(LocaleProvider::class);

        self::assertSame('fr', $localeProvider->getCurrentLocale());
    }

    public function testReturnsOverrideLocaleWhenSet(): void
    {
        self::bootKernel();

        $localeProvider = self::getContainer()->get(LocaleProvider::class);
        $localeProvider->setLocale('en');

        self::assertSame('en', $localeProvider->getCurrentLocale());
    }

    public function testReturnsRequestLocaleFromRequest(): void
    {
        self::bootKernel();

        $request = new Request();
        $request->setLocale('en');

        $requestStack = self::getContainer()->get(RequestStack::class);
        $requestStack->push($request);

        $localeProvider = self::getContainer()->get(LocaleProvider::class);

        self::assertSame('en', $localeProvider->getCurrentLocale());
    }

    public function testOverrideLocaleTakesPriorityOverRequest(): void
    {
        self::bootKernel();

        $request = new Request();
        $request->setLocale('fr');

        $requestStack = self::getContainer()->get(RequestStack::class);
        $requestStack->push($request);

        $localeProvider = self::getContainer()->get(LocaleProvider::class);
        $localeProvider->setLocale('en');

        self::assertSame('en', $localeProvider->getCurrentLocale());
    }
}
