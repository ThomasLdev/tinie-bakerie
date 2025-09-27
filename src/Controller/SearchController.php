<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository
    ) {
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $locale = $request->getLocale();
        $posts = [];

        if (!empty(trim($query))) {
            // Use the ranking version for better results
            $posts = $this->postRepository->searchPostsWithRanking($query, $locale);
        }

        return $this->render('search/index.html.twig', [
            'posts' => $posts,
            'query' => $query,
            'total' => count($posts),
        ]);
    }

    #[Route('/api/search', name: 'api_search', methods: ['GET'])]
    public function apiSearch(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $locale = $request->query->get('locale');
        $posts = [];

        if (!empty(trim($query))) {
            $posts = $this->postRepository->searchPostsWithRanking($query, $locale);
        }

        // Transform posts to simple array for JSON response
        $results = array_map(function ($post) {
            $translation = $post->getTranslations()->first();
            $category = $post->getCategory();
            $categoryTranslation = $category ? $category->getTranslations()->first() : null;

            return [
                'id' => $post->getId(),
                'title' => $translation ? $translation->getTitle() : '',
                'slug' => $translation ? $translation->getSlug() : '',
                'excerpt' => $translation ? $translation->getExcerpt() : '',
                'category' => $categoryTranslation ? $categoryTranslation->getTitle() : null,
                'createdAt' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $posts);

        return $this->json([
            'results' => $results,
            'total' => count($results),
            'query' => $query,
        ]);
    }
}
