<?php

declare(strict_types=1);

namespace App\Services\Locale;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LocaleProvider
{
    private ?string $overrideLocale = null;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly string $defaultLocale = 'en',
    ) {
    }

    /**
     * Returns the current locale from various sources in this priority:
     * 1. Explicitly set override locale (for CLI commands)
     * 2. Request locale (for web requests)
     * 3. Default locale fallback.
     */
    public function getCurrentLocale(): string
    {
        if (null !== $this->overrideLocale) {
            return $this->overrideLocale;
        }

        $request = $this->requestStack->getCurrentRequest();

        if ($request instanceof Request) {
            return $request->getLocale();
        }

        return $this->defaultLocale;
    }

    /**
     * Explicitly sets the locale for the current context.
     * Useful in CLI commands or tests where there's no HTTP request.
     */
    public function setLocale(string $locale): void
    {
        $this->overrideLocale = $locale;
    }
}
