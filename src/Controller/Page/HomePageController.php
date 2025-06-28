<?php

namespace App\Controller\Page;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
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
    public function indexNoLocale(
        Request $request,
        #[Autowire(param: 'app.supported_locales')] string $supportedLocales,
        #[Autowire(param: 'default_locale')] string $defaultLocale,
    ): Response {
        return $this->redirectToRoute(
            'app_page_homepage_index',
            [
                '_locale' => $request->getPreferredLanguage(explode('|', $supportedLocales)) ?? $defaultLocale,
            ]
        );
    }
}
