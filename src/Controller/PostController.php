<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Services\Post\Model\ListPostModel;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('{_locale<%app.supported_locales%>}')]
final class PostController extends AbstractController
{
    public function __construct(private readonly PostRepository $repository)
    {
    }

    /**
     * @return array<'posts', ArrayCollection<array-key,ListPostModel>>
     */
    #[Route(['en' => '/post', 'fr' => '/article'], methods: ['GET'])]
    #[Template('post/index.html.twig')]
    public function index(): array
    {
        return [
            'posts' => $this->repository->getPublished(),
        ];
    }

    /**
     * @return array<'post', array<array-key, PostModel>>
     */
    #[Route(
        [
            'en' => '/post/{slugCategory}/{slugPost}',
            'fr' => '/article/{slugCategory}/{slugPost}',
        ],
        methods: ['GET']
    )]
    #[Template('post/show.html.twig')]
    public function show(string $slugCategory, string $slugPost): array
    {
        $viewPost = $this->repository->findOneBySlugAndLocale($slugPost);

        if ($slugCategory !== $viewPost->categorySlug) {
            throw $this->createNotFoundException();
        }

        return [
            'post' => $viewPost,
        ];
    }
}
