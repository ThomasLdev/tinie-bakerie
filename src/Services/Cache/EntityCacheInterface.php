<?php

declare(strict_types=1);

namespace App\Services\Cache;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('service.entity_cache')]
interface EntityCacheInterface {
    public function get(string $locale): array;
    public function getOne(string $locale, string $identifier): ?object;
    public function invalidate(object $entity): void;
    public function getEntityName(): string;
    public static function supports(object $entity): bool;
}
