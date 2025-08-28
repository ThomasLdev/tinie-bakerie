<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\TranslationEmbedType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
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

        yield TextField::new('title', 'admin.post.title');

        yield ArrayField::new('tags', 'admin.post.tags');

        yield AssociationField::new('category', 'admin.category.title')
            ->formatValue(function (Category $category) {
                return $category->getTitle();
            });

        yield DateField::new('createdAt', 'admin.post.created_at');

        yield DateField::new('updatedAt', 'admin.post.updated_at');
    }

    private function getFormFields(): Generator
    {
        yield AssociationField::new('tags', 'admin.post.tags')
            ->setFormTypeOption('choice_label', 'title')
            ->setFormTypeOption('by_reference', false);

        yield AssociationField::new('category', 'admin.category.title')
            ->setFormTypeOption('choice_label', 'title');

        yield FormField::addTab('Titre')
            ->setFormType(TranslationEmbedType::class)
            ->setFormTypeOptions([
                'properties' => [
                    'title' => [
                        "label" => 'Titre',
                        'formType' => TextType::class,
                        'rows' => 6,
                    ],
                ],
            ]);

        yield AssociationField::new('sections', 'admin.post.sections')
            ->setFormTypeOptions([
                'by_reference' => false,
                'choice_label' => 'id',
            ]);
    }
}
