<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension that adds data-test-id attributes for easier E2E/Functional testing.
 * Only renders in test environment to keep production HTML clean.
 */
class TestExtension extends AbstractExtension
{
    public function __construct(#[Autowire('%kernel.environment%')] private string $environment)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('test_id', $this->renderTestId(...)),
        ];
    }

    /**
     * Renders a data-test-id attribute only in test environment.
     *
     * Usage in Twig:
     *   <button {{ test_id('submit-button') }}>Submit</button>
     *   <div {{ test_id('post-' ~ post.id) }}>...</div>
     *
     * Renders in test:
     *   <button data-test-id="submit-button">Submit</button>
     *
     * Renders in prod/dev:
     *   <button>Submit</button>
     */
    public function renderTestId(string $identifier): string
    {
        if ('test' !== $this->environment) {
            return '';
        }

        return sprintf('data-test-id="%s"', htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8'));
    }
}
