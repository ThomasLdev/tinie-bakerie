<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Services\Post\Cache\PostCache;
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
        private readonly PostRepository $repository,
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
        return [
            'posts' => $this->cache->getLocalizedCachedPosts($request->getLocale()),
        ];
    }

    /**
     * @return array<'post',mixed>
     */
    #[Route(['en' => '/{categorySlug}/{postSlug}', 'fr' => '/{categorySlug}/{postSlug}'], methods: ['GET'])]
    #[Template('post/show.html.twig')]
    public function show(string $categorySlug, string $postSlug, Request $request): array
    {
        $post = $this->cache->getLocalizedCachedPost($request->getLocale(), $postSlug);

//        $post = $this->repository->findOneActive($postSlug);

        if (!$post instanceof Post) {
            throw $this->createNotFoundException();
        }

        if ($categorySlug !== $post->getCategory()?->getSlug()) {
            throw $this->createNotFoundException();
        }

        return [
            'post' => $post,
        ];
    }
}
