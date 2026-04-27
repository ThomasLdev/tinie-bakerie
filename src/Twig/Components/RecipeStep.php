<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\PostSectionMedia;
use App\Entity\RecipeStep as RecipeStepEntity;
use App\Services\Recipe\Enum\StepTipType;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class RecipeStep
{
    public RecipeStepEntity $step;

    public int $index;

    public function getNumber(): string
    {
        return str_pad((string) $this->index, 2, '0', \STR_PAD_LEFT);
    }

    public function getTipType(): ?StepTipType
    {
        return $this->step->getTipType();
    }

    public function getTipText(): string
    {
        return $this->step->getTipText();
    }

    public function getTipIcon(): string
    {
        return match ($this->step->getTipType()) {
            StepTipType::Warning => 'lucide:triangle-alert',
            default => 'lucide:lightbulb',
        };
    }

    public function getTipLabelKey(): string
    {
        return match ($this->step->getTipType()) {
            StepTipType::Warning => 'recipe.show.steps.warning',
            default => 'recipe.show.steps.tip',
        };
    }

    public function getCoverAttachment(): ?PostSectionMedia
    {
        $first = $this->step->getMedia()->first();

        return $first instanceof PostSectionMedia ? $first : null;
    }
}
