<?php

namespace App\Doctrine\Filter;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class LocaleFilter extends SQLFilter
{
    /**
     * @throws Exception
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if (!$targetEntity->hasField('locale')) {
            return '';
        }

        try {
            $locale = $this->getParameter('currentLocale');
        } catch (\InvalidArgumentException) {
            return '';
        }

        // Remove quotes from parameter (Doctrine adds them)
        $locale = trim($locale, "'");

        return sprintf(
            '%s.locale = %s',
            $targetTableAlias,
            $this->getConnection()->getDatabasePlatform()->quoteStringLiteral($locale)
        );
    }
}
