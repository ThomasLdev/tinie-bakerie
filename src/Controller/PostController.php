<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route([
    'en' => '{_locale<%app.supported_locales%>}/posts/',
    'fr' => '{_locale<%app.supported_locales%>}/articles/',
])]
final class PostController extends AbstractController
{
    public function __construct(private readonly PostRepository $repository)
    {
    }

    /**
     * @return array<'posts', ArrayCollection<array-key,Post>>
     */
    #[Route(methods: ['GET'])]
    #[Template('post/index.html.twig')]
    public function index(): array
    {
        return [
            'posts' => $this->repository->findAllPublished(),
        ];
    }

    /**
     * @return array<'post', array<array-key,Post>>
     */
    #[Route(['en' => '{categorySlug}/{postSlug}', 'fr' => '{categorySlug}/{postSlug}'], methods: ['GET'])]
    #[Template('post/show.html.twig')]
    public function show(string $categorySlug, string $postSlug): array
    {
        $post = $this->repository->findOnePublished($postSlug);

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
