<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Form\PostMediaType;
use App\Form\PostSectionType;
use App\Form\PostTranslationType;
use App\Services\Locale\Locales;
use App\Services\Post\Enum\Difficulty;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Post>
 */
class PostCrudController extends AbstractCrudController
{
    public function __construct(private readonly Locales $locales)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Post
    {
        $post = new Post();

        foreach ($this->locales->get() as $locale) {
            $post->addTranslation(new PostTranslation()->setLocale($locale));
        }

        return $post;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return $this->getIndexFields();
        }

        return $this->getFormFields();
    }

    #[\Override]
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

    private function getIndexFields(): \Generator
    {
        yield BooleanField::new('active', 'admin.post.active');

        yield TextField::new('title', 'admin.global.title');

        yield ArrayField::new('tags', 'admin.tag.dashboard.plural');

        yield AssociationField::new('category', 'admin.category.dashboard.singular')
            ->formatValue(static fn (?Category $category): string => $category?->getTitle() ?? '-');

        yield DateField::new('createdAt', 'admin.global.created_at');

        yield DateField::new('updatedAt', 'admin.global.updated_at');
    }

    private function getFormFields(): \Generator
    {
        yield BooleanField::new('active', 'admin.post.active');

        yield IntegerField::new('cookingTime', 'admin.post.cooking_time.label');

        yield ChoiceField::new('difficulty', 'admin.post.difficulty.label')
            ->setChoices(Difficulty::cases())
            ->renderExpanded()
            ->renderAsBadges([
                'easy' => 'success',
                'medium' => 'warning',
                'hard' => 'danger',
            ]);

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
                    'supported_locales' => $this->locales->get(),
                ],
            ])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded(false)
            ->setColumns('col-12');

        yield CollectionField::new('translations', 'admin.global.translations')
            ->setEntryType(PostTranslationType::class)
            ->setFormTypeOptions([
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'entry_options' => [
                    'supported_locales' => $this->locales->get(),
                ],
            ])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded(false)
            ->setColumns('col-12');

        yield CollectionField::new('sections', 'admin.post_section.title')
            ->setEntryType(PostSectionType::class)
            ->setFormTypeOptions([
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'entry_options' => [
                    'supported_locales' => $this->locales->get(),
                ],
            ])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded(false)
            ->setColumns('col-12');
    }
}
