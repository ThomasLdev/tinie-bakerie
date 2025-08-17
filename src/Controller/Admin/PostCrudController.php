<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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
        yield DateField::new('publishedAt', 'admin.post.published_at');

        // Index
        yield TextField::new('title', 'admin.post.title')
            ->hideOnForm();
        yield ArrayField::new('tags', 'admin.post.tags')
            ->hideOnForm();
        yield AssociationField::new('category', 'admin.category.title')
            ->formatValue(function (Category $category) {
                return $category->getTitle();
            })
            ->hideOnForm();
        yield DateField::new('createdAt', 'admin.post.created_at')
            ->hideOnForm();
        yield DateField::new('updatedAt', 'admin.post.updated_at')
            ->hideOnForm();

        // Edit
        yield TextField::new('title', 'admin.post.title')
            ->setFormTypeOption('attr', ['placeholder' => 'admin.post.title_placeholder']);
        yield TextField::new('slug', 'admin.post.slug')
            ->setFormTypeOption('attr', ['disabled' => 'true'])
            ->setFormTypeOption('empty_data', '')
            ->hideOnIndex();
        yield AssociationField::new('tags', 'admin.post.tags')
            ->setFormTypeOption('choice_label', 'title')
            ->setFormTypeOption('by_reference', false)
            ->hideOnIndex();
        yield AssociationField::new('category', 'admin.category.title')
            ->setFormTypeOption('choice_label', 'title')
            ->hideOnIndex();
    }
}
