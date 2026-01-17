<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route([
    'en' => '{_locale<%app.supported_locales%>}/categories',
    'fr' => '{_locale<%app.supported_locales%>}/categories',
])]
final class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @return array<'category',Category>
     */
    #[Route(['en' => '/{categorySlug}', 'fr' => '/{categorySlug}'], methods: ['GET'])]
    #[Template('category/show.html.twig')]
    public function show(string $categorySlug, Request $request): array
    {
        $category = $this->categoryRepository->findOne($categorySlug);

        if (!$category instanceof Category) {
            throw $this->createNotFoundException();
        }

        return ['category' => $category];
    }
}
