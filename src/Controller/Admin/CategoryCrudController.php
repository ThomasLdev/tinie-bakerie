<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryMediaType;
use App\Form\CategoryTranslationType;
use App\Services\Locale\Locales;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Category>
 */
class CategoryCrudController extends AbstractCrudController
{
    public function __construct(private readonly Locales $locales)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return $this->getIndexFields();
        }

        return $this->getFormFields($pageName);
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('admin.category.dashboard.singular')
            ->setEntityLabelInPlural('admin.category.dashboard.plural')
            ->setPageTitle('index', 'admin.category.dashboard.index')
            ->setPageTitle('new', 'admin.category.dashboard.create')
            ->setPageTitle('edit', 'admin.category.dashboard.edit')
            ->setPageTitle('detail', 'admin.category.dashboard.detail');
    }

    private function getIndexFields(): \Generator
    {
        yield TextField::new('title', 'admin.global.title')
            ->setColumns(12)
            ->setRequired(true);

        yield DateField::new('createdAt', 'admin.global.created_at');

        yield DateField::new('updatedAt', 'admin.global.updated_at');
    }

    private function getFormFields(string $pageName): \Generator
    {
        yield CollectionField::new('media', 'admin.global.media.label')
            ->setEntryType(CategoryMediaType::class)
            ->setFormTypeOptions([
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'entry_options' => [
                    'hidde_locale' => Crud::PAGE_EDIT === $pageName,
                    'supported_locales' => $this->locales->get(),
                ],
            ])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded(false)
            ->setColumns('col-12');

        yield CollectionField::new('translations', 'admin.global.translations')
            ->setEntryType(CategoryTranslationType::class)
            ->setFormTypeOptions([
                'by_reference' => false,
                'allow_add' => Crud::PAGE_EDIT !== $pageName,
                'allow_delete' => Crud::PAGE_EDIT !== $pageName,
                'prototype' => true,
                'entry_options' => [
                    'hidde_locale' => Crud::PAGE_EDIT === $pageName,
                    'supported_locales' => $this->locales->get(),
                ],
            ])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded(false)
            ->setColumns('col-12');
    }
}
