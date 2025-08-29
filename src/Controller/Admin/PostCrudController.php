<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\Field\PostTranslationsField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PostCrudController extends AbstractCrudController
{
    public function __construct(
        #[Autowire(param: 'app.non_default_locale')] private readonly array $nonDefaultLocale,
    )
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

        return $this->getFormFields();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Post')
            ->setEntityLabelInPlural('Posts')
            ->setPageTitle('index', 'Posts')
            ->setPageTitle('new', 'Create Post')
            ->setPageTitle('edit', 'Edit Post')
            ->setPageTitle('detail', 'Post Details');
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

        yield TextField::new('title', 'admin.global.title')
            ->setRequired(true)
        ;

        yield TextField::new('metaTitle', 'admin.global.meta_title')
            ->setRequired(false)
        ;

        yield TextField::new('metaDescription', 'admin.global.meta_description')
            ->setRequired(false)
        ;

        yield TextField::new('excerpt', 'admin.global.excerpt')
            ->setRequired(false)
        ;

        yield PostTranslationsField::new('translations', '')
            ->setLocales($this->nonDefaultLocale);

//        yield CollectionField::new('sections', 'admin.post.sections')
//            ->setEntryType(PostSectionFormType::class)
//            ->setColumns(12)
//            ->setFormTypeOptions([
//                'by_reference' => false,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'delete_empty' => true,
//                'prototype' => true,
//                'attr' => [
//                    'rows' => 12
//                ]
//            ])
//            ->allowAdd()
//            ->allowDelete()
//            ->renderExpanded();
    }
}
