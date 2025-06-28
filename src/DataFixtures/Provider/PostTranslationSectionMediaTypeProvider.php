<?php

namespace App\DataFixtures\Provider;

use App\Services\Media\Enum\MediaType;
use Faker\Provider\Base as BaseProvider;

class PostTranslationSectionMediaTypeProvider extends BaseProvider
{
    public function sectionMediaType(?string $specificValue = null): MediaType
    {
        if (null === $specificValue) {
            throw new \InvalidArgumentException('Specific value cannot be null.');
        }

        try {
            return MediaType::from($specificValue);
        } catch (\ValueError) {
            throw new \InvalidArgumentException(sprintf('Invalid section type: %s', $specificValue));
        }
    }
}
