<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait MediaAccessibility
{
    #[ORM\Column(type: Types::STRING, nullable: false, options: ['default' => ''])]
    private string $alt = '';

    #[ORM\Column(type: Types::STRING, nullable: false, options: ['default' => ''])]
    private string $title = '';

    public function getAlt(): string
    {
        return $this->alt;
    }

    public function setAlt(string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
