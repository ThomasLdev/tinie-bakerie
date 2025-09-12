<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Tag;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    //    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Tinie Bakerie');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('admin.website.label', 'fa fa-home', '/');
        yield MenuItem::linkToCrud('admin.post.dashboard.plural', 'fas fa-newspaper', Post::class);
        yield MenuItem::linkToCrud('admin.category.dashboard.plural', 'fas fa-layer-group', Category::class);
        yield MenuItem::linkToCrud('admin.tag.dashboard.plural', 'fas fa-tag', Tag::class);
    }
}
