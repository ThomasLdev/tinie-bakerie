<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Tag;
use JoliCode\MediaBundle\Model\Media;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class TagCard
{
    public Tag $tag;

    public function getImage(): ?Media
    {
        return $this->tag->getImage();
    }

    public function getRecipeCount(): int
    {
        return $this->tag->getPosts()->count();
    }
}
