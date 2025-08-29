<?php

declare(strict_types=1);

namespace App\Form\Field;

use App\Form\PostTranslationsType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

final class PostTranslationsField implements FieldInterface
{
    use FieldTrait;

    public const string OPTION_LOCALES = 'locales';

    public static function new(string $propertyName, ?string $label = null): self
    {
        return new self()
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath('admin/crud/field/post_translations')
            ->setFormType(PostTranslationsType::class);
    }

    public function setLocales(array $locales): self
    {
        $this->setFormTypeOption(self::OPTION_LOCALES, $locales);

        return $this;
    }
}
