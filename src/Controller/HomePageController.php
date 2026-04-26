<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Tag;
use App\Repository\RecipeRepository;
use App\Repository\TagRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomePageController extends AbstractController
{
    public function __construct(
        private readonly RecipeRepository $recipeRepository,
        private readonly TagRepository $tagRepository,
    ) {
    }

    /**
     * @return array{featuredRecipe: ?Recipe, featuredRecipes: list<Recipe>, featuredTags: list<Tag>}
     */
    #[Route('{_locale<%app.supported_locales%>}')]
    #[Template('page/home.html.twig')]
    public function index(): array
    {
        return [
            'featuredRecipe' => $this->recipeRepository->findLatestActive(),
            'featuredRecipes' => $this->recipeRepository->findFeatured(5),
            'featuredTags' => $this->tagRepository->findFeatured(5),
        ];
    }

    #[Route('/')]
    public function indexNoLocale(
        Request $request,
        #[Autowire(param: 'app.supported_locales')]
        string $supportedLocales,
        #[Autowire(param: 'default_locale')]
        string $defaultLocale,
    ): Response {
        return $this->redirectToRoute(
            'app_homepage_index',
            [
                '_locale' => $request->getPreferredLanguage(explode('|', $supportedLocales)) ?? $defaultLocale,
            ],
        );
    }
}
