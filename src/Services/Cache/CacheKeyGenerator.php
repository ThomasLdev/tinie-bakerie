<?php

declare(strict_types=1);

namespace App\Services\Cache;

final readonly class CacheKeyGenerator
{
    public function entityIndex(string $entityName, string $locale): string
    {
        return sprintf('%s_index_%s', strtolower($entityName), $locale);
    }

    public function entityShow(string $entityName, string $locale, string $identifier): string
    {
        return sprintf('%s_show_%s_%s', strtolower($entityName), $locale, $identifier);
    }
}
