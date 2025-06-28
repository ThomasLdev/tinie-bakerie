<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait MediaAccessibilityProperties
{
    #[ORM\Column(length: 255, nullable: false, options: ['default' => ''])]
    private string $alt = '';

    #[ORM\Column(length: 255, nullable: false, options: ['default' => ''])]
    private string $title = '';

    public function getAlt(): string
    {
        return $this->alt;
    }

    public function setAlt(string $alt): static
    {
        $this->alt = $alt;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
