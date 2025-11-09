<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Contracts\Translation;
use App\Services\Locale\Locales;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidTranslationsValidator extends ConstraintValidator
{
    public int $requiredCount;

    public function __construct(private readonly Locales $locales)
    {
        $this->requiredCount = \count($this->locales->get());
    }

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

        $entityName = $this->getEntityName();

        if ($value->count() !== $this->requiredCount) {
            $this->context->buildViolation($constraint->countMessage)
                ->setParameter('{{ count }}', (string) $value->count())
                ->setParameter('{{ required }}', (string) $this->requiredCount)
                ->setParameter('{{ entity }}', $entityName)
                ->addViolation();

            return;
        }

        $locales = [];

        foreach ($value as $translation) {
            if (!$translation instanceof Translation) {
                continue;
            }

            $locale = $translation->getLocale();

            if (\in_array($locale, $locales, true)) {
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

        if (!\is_object($object)) {
            return 'Entité';
        }

        $className = $object::class;

        return ucfirst(strtolower(substr($className, strrpos($className, '\\') + 1)));
    }
}
