<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class HeaderController extends AbstractController
{
    #[Route('/header', methods: ['GET'])]
    #[Template('page/layout/header.html.twig')]
    public function renderHeader(CategoryRepository $categoryRepository): array
    {
        return [
            'categories' => $categoryRepository->findAllSlugs(),
        ];
    }
}
