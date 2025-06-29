<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // Global fields
        yield BooleanField::new('enabled', 'app.post.enabled');

        // Index
        yield TextField::new('adminName', 'app.post.title')
            ->hideOnForm();
        yield ArrayField::new('tags', 'app.post.tags')
            ->hideOnForm();
        yield AssociationField::new('category', 'app.category.label')
            ->formatValue(function (Category $category) {
                return $category->getAdminName();
            })
            ->hideOnForm();
        yield DateField::new('createdAt', 'app.post.created_at')
            ->hideOnForm();

        // Edit
        yield AssociationField::new('tags', 'app.post.tags')
            ->setFormTypeOption('choice_label', 'adminName')
            ->setFormTypeOption('by_reference', false)
            ->hideOnIndex();
        yield AssociationField::new('category', 'app.category.label')
            ->setFormTypeOption('choice_label', 'adminName')
            ->hideOnIndex();
    }
}
