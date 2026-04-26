<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Footer
{
    public int $featuredLimit = 4;

    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

    /**
     * @return list<Category>
     */
    public function getFeaturedCategories(): array
    {
        return $this->categoryRepository->findFeatured($this->featuredLimit);
    }
}
