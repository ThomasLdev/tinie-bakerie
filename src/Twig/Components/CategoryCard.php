<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Category;
use App\Entity\CategoryMedia;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class CategoryCard
{
    public Category $category;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getCoverAttachment(): ?CategoryMedia
    {
        $first = $this->category->getMedia()->first();

        return $first instanceof CategoryMedia ? $first : null;
    }

    public function getHref(): string
    {
        return $this->urlGenerator->generate('app_category_show', [
            'categorySlug' => $this->category->getSlug(),
        ]);
    }

    public function getRecipeCount(): int
    {
        return $this->category->getPosts()->count();
    }
}
