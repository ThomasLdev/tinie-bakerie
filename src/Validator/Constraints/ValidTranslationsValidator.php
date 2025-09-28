<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Contracts\IsTranslation;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidTranslationsValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidTranslations) {
            throw new UnexpectedTypeException($constraint, ValidTranslations::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Collection) {
            throw new UnexpectedTypeException($value, Collection::class);
        }

        // Get entity name from context
        $entityName = $this->getEntityName();

        // Check if we have the required number of translations
        if ($value->count() !== $constraint->requiredCount) {
            $this->context->buildViolation($constraint->countMessage)
                ->setParameter('{{ count }}', (string) $value->count())
                ->setParameter('{{ required }}', (string) $constraint->requiredCount)
                ->setParameter('{{ entity }}', $entityName)
                ->addViolation();
            return;
        }

        // Check locale uniqueness
        $locales = [];
        foreach ($value as $translation) {
            if (!$translation instanceof IsTranslation) {
                continue;
            }

            $locale = $translation->getLocale();
            if (in_array($locale, $locales, true)) {
                $this->context->buildViolation($constraint->localeMessage)
                    ->setParameter('{{ locale }}', $locale)
                    ->setParameter('{{ entity }}', $entityName)
                    ->addViolation();
                return;
            }
            $locales[] = $locale;
        }
    }

    private function getEntityName(): string
    {
        $object = $this->context->getObject();
        if (!is_object($object)) {
            return 'Entité';
        }

        $className = get_class($object);
        $shortClassName = substr($className, strrpos($className, '\\') + 1);

        // Convert class name to more readable format with proper capitalization
        return match ($shortClassName) {
            'Post' => 'L\'article',
            'PostMedia' => 'Un média',
            'Category' => 'Une catégorie',
            'Tag' => 'Une étiquette',
            default => ucfirst(strtolower($shortClassName)),
        };
    }
}
