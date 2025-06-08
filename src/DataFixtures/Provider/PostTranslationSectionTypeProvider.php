<?php

namespace App\DataFixtures\Provider;

use App\Services\PostTranslation\Enum\PostTranslationSectionType;
use Faker\Provider\Base as BaseProvider;
use InvalidArgumentException;
use ValueError;

class PostTranslationSectionTypeProvider extends BaseProvider
{
    public function sectionType(?string $specificValue = null): PostTranslationSectionType
    {
        if (null === $specificValue) {
            throw new InvalidArgumentException('Specific value cannot be null.');
        }

        try {
            return PostTranslationSectionType::from($specificValue);
        } catch (ValueError) {
            throw new InvalidArgumentException(sprintf('Invalid section type: %s', $specificValue));
        }
    }
}
