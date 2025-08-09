<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Use with LocalizedEntity trait.
 */
trait MediaAccessibility
{
    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::STRING, nullable: false, options: ['default' => ''])]
    private string $alt = '';

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::STRING, nullable: false, options: ['default' => ''])]
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
