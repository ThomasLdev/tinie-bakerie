<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\PostMediaType;
use App\Form\PostSectionType;
use App\Form\PostTranslationType;
use App\Services\Locale\Locales;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;

class PostCrudController extends AbstractCrudController
{
    public function __construct(private readonly Locales $locales)
    {
    }

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

        yield CollectionField::new('media', 'admin.global.media.label')
            ->setEntryType(PostMediaType::class)
            ->setFormTypeOptions([
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'entry_options' => [
                    'supported_locales' => $this->locales->get()
                ],
            ])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded(false)
            ->setColumns('col-12')
        ;

        yield CollectionField::new('translations', 'admin.global.translations')
            ->setEntryType(PostTranslationType::class)
            ->setFormTypeOptions([
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'entry_options' => [
                    'supported_locales' => $this->locales->get()
                ]
            ])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded(false)
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
                    'supported_locales' => $this->locales->get()
                ]
            ])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded(false)
            ->setColumns('col-12')
        ;
    }
}
