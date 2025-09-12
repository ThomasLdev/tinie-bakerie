<?php

namespace App\Controller;

use App\Entity\Post;
use App\Services\Cache\PostCache;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route([
    'en' => '{_locale<%app.supported_locales%>}/posts',
    'fr' => '{_locale<%app.supported_locales%>}/articles',
])]
final class PostController extends AbstractController
{
    public function __construct(
        private readonly PostCache $cache,
    ) {
    }

    /**
     * @return array<'posts', array<array-key,mixed>>
     *
     * @throws InvalidArgumentException
     */
    #[Route(methods: ['GET'])]
    #[Template('post/index.html.twig')]
    public function index(Request $request): array
    {
        return ['posts' => $this->cache->get($request->getLocale())];
    }

    /**
     * @return array<'post',Post>
     *
     * @throws InvalidArgumentException
     */
    #[Route(['en' => '/{categorySlug}/{postSlug}', 'fr' => '/{categorySlug}/{postSlug}'], methods: ['GET'])]
    #[Template('post/show.html.twig')]
    public function show(string $categorySlug, string $postSlug, Request $request): array
    {
        $post = $this->cache->getOne($request->getLocale(), $postSlug);

        if (!$post instanceof Post) {
            throw $this->createNotFoundException();
        }

        if ($categorySlug !== $post->getCategory()?->getSlug()) {
            throw $this->createNotFoundException();
        }

        return ['post' => $post];
    }
}
