<?php

namespace App\DataFixtures\Provider;

use App\Services\PostSection\Enum\PostSectionType;
use Faker\Provider\Base as BaseProvider;
use InvalidArgumentException;
use ValueError;

class PostTranslationSectionTypeProvider extends BaseProvider
{
    public function sectionType(?string $specificValue = null): PostSectionType
    {
        if (null === $specificValue) {
            throw new InvalidArgumentException('Specific value cannot be null.');
        }

        try {
            return PostSectionType::from($specificValue);
        } catch (ValueError) {
            throw new InvalidArgumentException(sprintf('Invalid section type: %s', $specificValue));
        }
    }
}
