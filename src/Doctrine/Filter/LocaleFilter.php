<?php
namespace App\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use InvalidArgumentException;

class LocaleFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if (!$targetEntity->hasField('locale')) {
            return '';
        }

        try {
            $locale = $this->getParameter('currentLocale');
        } catch (InvalidArgumentException) {
            return '';
        }

        // Remove quotes from parameter (Doctrine adds them)
        $locale = trim($locale, "'");

        return sprintf('%s.locale = %s', $targetTableAlias, $this->getConnection()->quote($locale));
    }
}

