<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidTranslations extends Constraint
{
    public string $message = 'translations.invalid';

    public string $countMessage = 'translations.count';

    public string $localeMessage = 'translations.locale_unique';

    #[\Override]
    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
