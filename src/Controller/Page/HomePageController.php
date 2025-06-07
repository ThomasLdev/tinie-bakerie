<?php

namespace App\Controller\Page;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_page_home_index')]
    #[Template('page/home/index.html.twig')]
    public function index(): array
    {
        return [];
    }
}
