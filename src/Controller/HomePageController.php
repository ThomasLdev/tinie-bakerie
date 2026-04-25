<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomePageController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
    ) {
    }

    /**
     * @return array{featuredPost: ?Post}
     */
    #[Route('{_locale<%app.supported_locales%>}')]
    #[Template('page/home.html.twig')]
    public function index(string $_locale): array
    {
        return [
            'featuredPost' => $this->postRepository->findLatestActive($_locale),
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
