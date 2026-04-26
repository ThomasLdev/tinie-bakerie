<?php

declare(strict_types=1);

namespace App\Entity;

use App\Services\Recipe\Enum\StepTipType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RecipeStep extends PostSection
{
    #[ORM\Column(enumType: StepTipType::class, nullable: true)]
    private ?StepTipType $tipType = null;

    public function getTipType(): ?StepTipType
    {
        return $this->tipType;
    }

    public function setTipType(?StepTipType $tipType): self
    {
        $this->tipType = $tipType;

        return $this;
    }

    #[\Override]
    public function getCurrentTranslation(): ?RecipeStepTranslation
    {
        $translation = $this->getTranslationForCurrentLocale();

        return $translation instanceof RecipeStepTranslation ? $translation : null;
    }

    public function getTipText(): string
    {
        return $this->getCurrentTranslation()?->getTipText() ?? '';
    }
}
