<?php

declare(strict_types=1);

namespace App\Services\Filter;

use App\Entity\Contracts\IsTranslation;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class LocaleFilter extends SQLFilter
{
    private const string PARAMETER_NAME = 'locale';

    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $reflexionClass = $targetEntity->getReflectionClass();

        if (!$reflexionClass?->implementsInterface(IsTranslation::class)) {
            return '';
        }

        return \sprintf(
            '%s.%s = %s',
            $targetTableAlias,
            self::PARAMETER_NAME,
            $this->getParameter(self::PARAMETER_NAME),
        );
    }
}
