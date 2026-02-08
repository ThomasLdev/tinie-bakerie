<?php

declare(strict_types=1);

namespace App\Cache;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

#[AsDecorator('joli_media.cache_warmer.media_entity_metadata')]
readonly class OptionalMediaEntityMetadataWarmer implements CacheWarmerInterface
{
    public function __construct(
        private CacheWarmerInterface $inner,
    ) {
    }

    public function isOptional(): bool
    {
        return true;
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        return $this->inner->warmUp($cacheDir, $buildDir);
    }
}
