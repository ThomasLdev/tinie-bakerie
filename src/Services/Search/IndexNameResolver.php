<?php

declare(strict_types=1);

namespace App\Services\Search;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class IndexNameResolver
{
    public function __construct(
        #[Autowire(param: 'meilisearch.prefix')]
        private string $prefix,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function resolve(string $entityShortName, string $locale): string
    {
        return \sprintf('%s%s_%s', $this->prefix, strtolower($entityShortName), $locale);
    }
}
