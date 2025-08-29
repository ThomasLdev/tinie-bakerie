<?php

declare(strict_types=1);

namespace App\Form\Field;

use App\Form\PostTranslationFormType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

final class TranslationsField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_LOCALES = 'locales';

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplateName('crud/field/translations')
            ->setFormType(CollectionType::class)
            ->setCustomOption(self::OPTION_LOCALES, ['en', 'fr']); // Adjust based on your supported locales
    }

    public function setLocales(array $locales): self
    {
        $this->setCustomOption(self::OPTION_LOCALES, $locales);
        return $this;
    }

    public function configureFormType(): void
    {
        $locales = $this->getCustomOption(self::OPTION_LOCALES);

        $this->setFormTypeOptions([
            'entry_type' => PostTranslationFormType::class,
            'entry_options' => function($data, $key) use ($locales) {
                return [
                    'locale' => $locales[$key] ?? 'en',
                ];
            },
            'allow_add' => false,
            'allow_delete' => false,
            'by_reference' => false,
            'data' => function($entity) use ($locales) {
                // Create data array for each locale
                $data = [];
                foreach ($locales as $locale) {
                    $data[$locale] = $entity;
                }
                return $data;
            },
        ]);
    }
}
