<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Recipe;
use App\Repository\CategoryRepository;
use App\Repository\RecipeRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route([
    'en' => '{_locale<%app.supported_locales%>}/recipes',
    'fr' => '{_locale<%app.supported_locales%>}/recettes',
])]
final class RecipeController extends AbstractController
{
    public function __construct(
        private readonly RecipeRepository $recipeRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @return array{recipes: list<Recipe>, categories: list<Category>}
     */
    #[Route(methods: ['GET'])]
    #[Template('recipe/index.html.twig')]
    public function index(): array
    {
        return [
            'recipes' => $this->recipeRepository->findAllActive(),
            'categories' => $this->categoryRepository->findAllSlugs(),
        ];
    }

    /**
     * @return array<'recipe',Recipe>
     */
    #[Route(['en' => '/{categorySlug}/{recipeSlug}', 'fr' => '/{categorySlug}/{recipeSlug}'], methods: ['GET'])]
    #[Template('recipe/show.html.twig')]
    public function show(string $categorySlug, string $recipeSlug): array
    {
        $recipe = $this->recipeRepository->findOneActive($recipeSlug);

        if (!$recipe instanceof Recipe) {
            throw $this->createNotFoundException();
        }

        if ($categorySlug !== $recipe->getCategory()?->getSlug()) {
            throw $this->createNotFoundException();
        }

        return ['recipe' => $recipe];
    }
}
