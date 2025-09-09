<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\PostSectionType;
use App\Form\PostTranslationType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;

class PostCrudController extends LocalizedCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return $this->getIndexFields();
        }

        return $this->getFormFields($pageName);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('admin.post.dashboard.singular')
            ->setEntityLabelInPlural('admin.post.dashboard.plural')
            ->setPageTitle('index', 'admin.post.dashboard.index')
            ->setPageTitle('new', 'admin.post.dashboard.create')
            ->setPageTitle('edit', 'admin.post.dashboard.edit')
            ->setPageTitle('detail', 'admin.post.dashboard.detail');
    }

    private function getIndexFields(): Generator
    {
        yield BooleanField::new('active', 'admin.post.active');

        yield TextField::new('title', 'admin.global.title');

        yield ArrayField::new('tags', 'admin.tag.dashboard.plural');

        yield AssociationField::new('category', 'admin.category.dashboard.singular')
            ->formatValue(function (Category $category) {
                return $category->getTitle();
            });

        yield DateField::new('createdAt', 'admin.global.created_at');

        yield DateField::new('updatedAt', 'admin.global.updated_at');
    }

    private function getFormFields(string $pageName): Generator
    {
        yield BooleanField::new('active', 'admin.post.active');

        yield AssociationField::new('tags', 'admin.tag.dashboard.plural')
            ->setFormTypeOption('choice_label', 'title')
            ->setFormTypeOption('by_reference', false);

        yield AssociationField::new('category', 'admin.category.dashboard.singular')
            ->setFormTypeOption('choice_label', 'title');

        yield CollectionField::new('translations', 'admin.global.translations')
            ->setEntryType(PostTranslationType::class)
            ->setFormTypeOptions([
                'by_reference' => false,
                'allow_add' => Crud::PAGE_EDIT !== $pageName,
                'allow_delete' => Crud::PAGE_EDIT !== $pageName,
                'prototype' => true,
                'entry_options' => [
                    'hidde_locale' => Crud::PAGE_EDIT === $pageName,
                    'supported_locales' => $this->getSupportedLocales()
                ]
            ])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded()
            ->setColumns('col-12')
        ;

        yield CollectionField::new('sections', 'admin.post_section.title')
            ->setEntryType(PostSectionType::class)
            ->setFormTypeOptions([
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'entry_options' => [
                    'hidde_locale' => false,
                    'supported_locales' => $this->getSupportedLocales()
                ]
            ])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded()
            ->setColumns('col-12')
        ;
    }
}
