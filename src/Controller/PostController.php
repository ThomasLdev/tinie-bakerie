<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Services\Post\Model\ViewPost;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('{_locale<%app.supported_locales%>}', name: 'app_post_')]
final class PostController extends AbstractController
{
    public function __construct(private readonly PostRepository $repository)
    {
    }

    #[Route(['en' => '/post', 'fr' => '/article'], name: 'index', methods: ['GET'])]
    public function index(string $_locale): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $this->repository->findAllByLocale(),
        ]);
    }

    #[Route(
        [
            'en' => '/post/{slugCategory}/{slugPost}',
            'fr' => '/article/{slugCategory}/{slugPost}'
        ],
        name: 'show',
        methods: ['GET']
    )]
    public function show(string $slugCategory, string $slugPost): Response
    {
        $postDTO = $this->repository->findOneBySlugAndLocale($slugPost);

        if (!$postDTO instanceof ViewPost || $postDTO->categorySlug !== $slugCategory) {
            throw $this->createNotFoundException();
        }

        return $this->render('post/show.html.twig', [
            'post' => $postDTO,
        ]);
    }
}
