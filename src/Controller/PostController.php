<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Services\Post\PostDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('{_locale}', name: 'app_post_', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
final class PostController extends AbstractController
{
    public function __construct(private readonly PostRepository $repository)
    {
    }

    #[Route(['en' => '/post', 'fr' => '/article'], name: 'index', methods: ['GET'])]
    public function index(string $_locale): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $this->repository->findAllByLocale($_locale),
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
    public function show(string $slugCategory, string $slugPost, string $_locale): Response
    {
        $postDTO = $this->repository->findOneBySlugAndLocale($slugPost, $_locale);

        if (!$postDTO instanceof PostDTO || $postDTO->category->getSlug() !== $slugCategory) {
            throw $this->createNotFoundException();
        }

        return $this->render('post/show.html.twig', [
            'post' => $postDTO,
        ]);
    }
}
