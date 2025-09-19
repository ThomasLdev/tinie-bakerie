<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route([
    'en' => '{_locale<%app.supported_locales%>}/about',
    'fr' => '{_locale<%app.supported_locales%>}/a-propos',
])]
class AboutController extends AbstractController
{
    /**
     * @return array<string, mixed>
     */
    #[Route(methods: ['GET'])]
    #[Template('page/about.html.twig')]
    public function index(): array
    {
        return [];
    }
}
