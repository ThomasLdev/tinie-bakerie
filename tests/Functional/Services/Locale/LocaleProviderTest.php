<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services\Locale;

use App\Services\Locale\LocaleProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Functional tests for LocaleProvider service.
 *
 * @internal
 */
#[CoversClass(LocaleProvider::class)]
final class LocaleProviderTest extends KernelTestCase
{
    private LocaleProvider $localeProvider;

    private RequestStack $requestStack;

    protected function setUp(): void
    {
        self::bootKernel();

        $localeProvider = self::getContainer()->get(LocaleProvider::class);
        \assert($localeProvider instanceof LocaleProvider);
        $this->localeProvider = $localeProvider;

        $requestStack = self::getContainer()->get(RequestStack::class);
        \assert($requestStack instanceof RequestStack);
        $this->requestStack = $requestStack;
    }

    protected function tearDown(): void
    {
        // Clean up any pushed requests
        while ($this->requestStack->getCurrentRequest() !== null) {
            $this->requestStack->pop();
        }

        parent::tearDown();
    }

    #[TestDox('Returns locale from current request')]
    #[DataProvider('provideRequestLocales')]
    public function testReturnsLocaleFromRequest(string $locale): void
    {
        $request = new Request();
        $request->setLocale($locale);
        $this->requestStack->push($request);

        self::assertSame($locale, $this->localeProvider->getCurrentLocale());
    }

    public static function provideRequestLocales(): \Generator
    {
        yield 'French locale' => ['fr'];
        yield 'English locale' => ['en'];
        yield 'German locale' => ['de'];
        yield 'Spanish locale' => ['es'];
    }

    #[TestDox('Returns default locale when no request exists')]
    public function testReturnsDefaultLocaleWhenNoRequest(): void
    {
        // Ensure no request is on the stack
        while ($this->requestStack->getCurrentRequest() !== null) {
            $this->requestStack->pop();
        }

        // Default locale is 'en' as configured
        self::assertSame('en', $this->localeProvider->getCurrentLocale());
    }

    #[TestDox('Override locale takes precedence over request locale')]
    public function testOverrideLocaleTakesPrecedence(): void
    {
        $request = new Request();
        $request->setLocale('fr');
        $this->requestStack->push($request);

        $this->localeProvider->setLocale('de');

        self::assertSame('de', $this->localeProvider->getCurrentLocale());
    }

    #[TestDox('Override locale takes precedence when no request exists')]
    public function testOverrideLocaleWorksWithoutRequest(): void
    {
        // Ensure no request is on the stack
        while ($this->requestStack->getCurrentRequest() !== null) {
            $this->requestStack->pop();
        }

        $this->localeProvider->setLocale('fr');

        self::assertSame('fr', $this->localeProvider->getCurrentLocale());
    }

    #[TestDox('setLocale allows changing the override locale')]
    public function testSetLocaleChangesOverride(): void
    {
        $this->localeProvider->setLocale('fr');
        self::assertSame('fr', $this->localeProvider->getCurrentLocale());

        $this->localeProvider->setLocale('en');
        self::assertSame('en', $this->localeProvider->getCurrentLocale());

        $this->localeProvider->setLocale('de');
        self::assertSame('de', $this->localeProvider->getCurrentLocale());
    }
}
