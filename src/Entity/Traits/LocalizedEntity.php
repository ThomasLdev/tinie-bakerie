<?php

namespace App\Entity\Traits;

use Gedmo\Mapping\Annotation as Gedmo;

trait LocalizedEntity
{
    #[Gedmo\Locale]
    private $locale; // @phpstan-ignore-line Let Gedmo handle the type

    public function setTranslatableLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTranslatableLocale(): ?string
    {
        return $this->locale;
    }
}
