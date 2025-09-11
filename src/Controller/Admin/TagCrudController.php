<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Tag;
use App\Form\TagTranslationType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TagCrudController extends LocalizedCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tag::class;
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
            ->setEntityLabelInSingular('admin.tag.dashboard.singular')
            ->setEntityLabelInPlural('admin.tag.dashboard.plural')
            ->setPageTitle('index', 'admin.tag.dashboard.index')
            ->setPageTitle('new', 'admin.tag.dashboard.create')
            ->setPageTitle('edit', 'admin.tag.dashboard.edit')
            ->setPageTitle('detail', 'admin.tag.dashboard.detail');
    }

    private function getIndexFields(): \Generator
    {
        yield ColorField::new('color', 'admin.tag.color.title');

        yield TextField::new('title', 'admin.global.title')
            ->setColumns(12)
            ->setRequired(true);

        yield DateField::new('createdAt', 'admin.global.created_at');

        yield DateField::new('updatedAt', 'admin.global.updated_at');
    }

    private function getFormFields(string $pageName): \Generator
    {
        yield ColorField::new('color', 'admin.tag.color.title');

        yield CollectionField::new('translations', 'admin.global.translations')
            ->setEntryType(TagTranslationType::class)
            ->setFormTypeOptions([
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'entry_options' => [
                    'supported_locales' => $this->getSupportedLocales()
                ]
            ])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded(false)
            ->setColumns('col-12')
        ;
    }
}
