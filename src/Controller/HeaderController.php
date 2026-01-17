<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class HeaderController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @return array{categories: array<mixed>}
     */
    #[Route('/header', methods: ['GET'])]
    #[Template('page/layout/header.html.twig')]
    public function renderHeader(): array
    {
        return [
            'categories' => $this->categoryRepository->findAllSlugs(),
        ];
    }
}
