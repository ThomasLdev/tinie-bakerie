<?php

declare(strict_types=1);

namespace App\Factory\Contracts;

use Faker\Generator;

/**
 * Interface for translation factories that need locale-specific faker data.
 *
 * Factories implementing this interface should return an array of
 * locale-sensitive default values using the provided Faker instance.
 */
interface LocaleAwareFactory
{
    /**
     * Returns locale-specific default values for the translation entity.
     *
     * This method should return only the fields that need locale-specific
     * fake data (e.g., title, description). Other fields like createdAt,
     * updatedAt should remain in the regular defaults() method.
     *
     * @return array<string, mixed>
     */
    public static function defaultsForLocale(Generator $faker): array;
}
