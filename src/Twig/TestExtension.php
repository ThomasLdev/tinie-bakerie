<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Attribute\AsTwigFunction;
use Twig\Markup;

/**
 * Twig extension that adds data-test-id attributes for easier E2E/Functional testing.
 * Only renders in test environment to keep production HTML clean.
 */
readonly class TestExtension
{
    public function __construct(#[Autowire('%kernel.environment%')] private string $environment)
    {
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
    #[AsTwigFunction('test_id')]
    public function renderTestId(string $identifier): string|Markup
    {
        if ('test' !== $this->environment) {
            return '';
        }

        $attribute = \sprintf(
            'data-test-id="%s"',
            htmlspecialchars($identifier, \ENT_COMPAT, 'UTF-8'),
        );

        return new Markup($attribute, 'UTF-8');
    }
}
