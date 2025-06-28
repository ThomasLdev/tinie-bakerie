<?php

namespace App\Controller\Page;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomePageController extends AbstractController
{
    /**
     * @return array<string, mixed>
     */
    #[Route('{_locale<%app.supported_locales%>}/')]
    #[Template('page/home/index.html.twig')]
    public function index(): array
    {
        return [];
    }

    #[Route('/')]
    public function indexNoLocale(): Response
    {
        return $this->redirectToRoute('app_page_homepage_index', ['_locale' => 'en']);
    }
}
