<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Attribute\AsTwigFunction;
use Twig\Markup;

/**
 * Twig extension that adds data-test-id attributes for easier E2E/Functional testing.
 * Renders in any non-production environment so tests run against `dev` (E2E browser)
 * and `test` (PHPUnit functional) consistently. Stripped in `prod` to keep HTML clean.
 */
readonly class TestExtension
{
    public function __construct(#[Autowire('%kernel.environment%')] private string $environment)
    {
    }

    /**
     * Renders a data-test-id attribute outside of production.
     *
     * Usage in Twig:
     *   <button {{ test_id('submit-button') }}>Submit</button>
     *   <div {{ test_id('post-' ~ post.id) }}>...</div>
     *
     * Renders in dev/test:
     *   <button data-test-id="submit-button">Submit</button>
     *
     * Renders in prod:
     *   <button>Submit</button>
     */
    #[AsTwigFunction('test_id')]
    public function renderTestId(string $identifier): string|Markup
    {
        if ('prod' === $this->environment) {
            return '';
        }

        $attribute = \sprintf(
            'data-test-id="%s"',
            htmlspecialchars($identifier, \ENT_COMPAT, 'UTF-8'),
        );

        return new Markup($attribute, 'UTF-8');
    }
}
