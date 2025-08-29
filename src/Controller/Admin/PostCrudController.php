<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\PostSectionFormType;
use App\Form\TranslationEmbedType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PostCrudController extends AbstractCrudController
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

        return $this->getFormFields();
    }

    private function getIndexFields(): Generator
    {
        yield BooleanField::new('active', 'admin.post.active');

        yield TextField::new('title', 'admin.global.title');

        yield ArrayField::new('tags', 'admin.post.tags');

        yield AssociationField::new('category', 'admin.category.title')
            ->formatValue(function (Category $category) {
                return $category->getTitle();
            });

        yield DateField::new('createdAt', 'admin.global.created_at');

        yield DateField::new('updatedAt', 'admin.global.updated_at');
    }

    private function getFormFields(): Generator
    {
        yield BooleanField::new('active', 'admin.post.active');

        yield AssociationField::new('tags', 'admin.post.tags')
            ->setFormTypeOption('choice_label', 'title')
            ->setFormTypeOption('by_reference', false);

        yield AssociationField::new('category')
            ->setFormTypeOption('choice_label', 'title');

        yield FormField::addTab('Titre')
            ->setFormType(TranslationEmbedType::class)
            ->setColumns(12)
            ->setFormTypeOptions([
                'properties' => [
                    'title' => [
                        "label" => 'Titre',
                        'fieldType' => TextType::class,
                        'rows' => 6,
                    ],
                ],
            ]);

        yield CollectionField::new('sections', 'admin.post.sections')
            ->setEntryType(PostSectionFormType::class)
            ->setColumns(12)
            ->setFormTypeOptions([
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'attr' => [
                    'rows' => 12
                ]
            ])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded();
    }
}
