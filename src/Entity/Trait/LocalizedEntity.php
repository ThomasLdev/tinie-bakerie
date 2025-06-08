<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait LocalizedEntity
{
    #[ORM\Column(length: 255)]
    private ?string $locale = null;

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }
}
