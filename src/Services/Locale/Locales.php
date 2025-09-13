<?php

declare(strict_types=1);

namespace App\Services\Locale;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class Locales
{
    public function __construct(#[Autowire(param: 'app.supported_locales')] private string $supportedLocales)
    {
    }

    /**
     * @return array<string>
     */
    public function get(): array
    {
        return explode('|', $this->supportedLocales);
    }
}
