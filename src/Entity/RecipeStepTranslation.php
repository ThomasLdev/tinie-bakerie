<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RecipeStepTranslation extends PostSectionTranslation
{
    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    private string $tipText = '';

    public function getTipText(): string
    {
        return $this->tipText;
    }

    public function setTipText(string $tipText): self
    {
        $this->tipText = $tipText;

        return $this;
    }
}
