<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RecipeTranslation extends PostTranslation
{
    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    private string $notes = '';

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    private string $chefNoteTitle = '';

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getChefNoteTitle(): string
    {
        return $this->chefNoteTitle;
    }

    public function setChefNoteTitle(string $chefNoteTitle): self
    {
        $this->chefNoteTitle = $chefNoteTitle;

        return $this;
    }
}
