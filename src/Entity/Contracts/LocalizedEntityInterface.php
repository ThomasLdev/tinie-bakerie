<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

interface LocalizedEntityInterface
{
    public function getTranslatableLocale(): ?string;

    public function setTranslatableLocale(string $locale): self;

    public function addTranslation(AbstractPersonalTranslation $translation): self;
}
