<?php

namespace App\DataFixtures\Provider;

use App\Entity\Enum\PostTranslationSectionMediaType;
use Faker\Provider\Base as BaseProvider;
use InvalidArgumentException;
use ValueError;

class PostTranslationSectionMediaTypeProvider extends BaseProvider
{
    public function sectionMediaType(?string $specificValue = null): PostTranslationSectionMediaType
    {
        if (null === $specificValue) {
            throw new InvalidArgumentException('Specific value cannot be null.');
        }

        try {
            return PostTranslationSectionMediaType::from($specificValue);
        } catch (ValueError) {
            throw new InvalidArgumentException(sprintf('Invalid section type: %s', $specificValue));
        }
    }
}
