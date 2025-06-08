<?php

namespace App\Controller;

use App\Entity\PostTranslation;
use App\Repository\PostRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'app_post_')]
final class PostController extends AbstractController
{
    #[Route('/post', name: 'index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route(
        '{_locale}/post/{slugCategory}/{slugPost}',
        name: 'show',
        requirements: ['_locale' => 'en|fr'],
        defaults: ['_locale' => 'en'],
        methods: ['GET']
    )]
    public function show(#[MapEntity(mapping: ['slugPost' => 'slug'])] PostTranslation $postTranslation): Response
    {
        return $this->render('post/show.html.twig', [
            'postTranslation' => $postTranslation,
        ]);
    }
}
