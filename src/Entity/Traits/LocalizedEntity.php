<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait LocalizedEntity
{
    #[ORM\Column(length: 2, nullable: false, options: ['default' => 'en'])]
    private string $locale = 'en';

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }
}
