<?php

namespace App\Services\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class LocaleFilter extends SQLFilter
{
    private const string PARAMETER_NAME = 'locale';

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if (!$targetEntity->hasField(self::PARAMETER_NAME)) {
            return '';
        }

        return sprintf(
            '%s.%s = %s',
            $targetTableAlias,
            self::PARAMETER_NAME,
            $this->getParameter(self::PARAMETER_NAME)
        );
    }
}
