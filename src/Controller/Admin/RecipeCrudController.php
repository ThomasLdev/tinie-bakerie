<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Recipe;
use App\Entity\RecipeTranslation;
use App\Form\Type\IngredientGroupType;
use App\Form\Type\PostMediaType;
use App\Form\Type\PostSectionType;
use App\Form\Type\RecipeStepType;
use App\Form\Type\RecipeTranslationType;
use App\Services\Locale\Locales;
use App\Services\Post\Enum\Difficulty;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;

/**
 * @extends AbstractCrudController<Recipe>
 */
class RecipeCrudController extends AbstractCrudController
{
    public function __construct(private readonly Locales $locales)
    {
    }

    #[\Override]
    public function configureAssets(Assets $assets): Assets
    {
        $joliMediaPackage = new PathPackage(
            '/bundles/jolimediaeasyadmin',
            new JsonManifestVersionStrategy(__DIR__ . '/../../../public/bundles/jolimediaeasyadmin/manifest.json'),
        );

        $eaPackage = new PathPackage(
            '/bundles/easyadmin',
            new JsonManifestVersionStrategy(__DIR__ . '/../../../public/bundles/easyadmin/manifest.json'),
        );

        return $assets
            ->addCssFile($joliMediaPackage->getUrl('joli-media-easy-admin.css'))
            ->addJsFile($joliMediaPackage->getUrl('joli-media-easy-admin.js'))
            ->addCssFile($eaPackage->getUrl('field-text-editor.css'))
            ->addJsFile($eaPackage->getUrl('field-text-editor.js'));
    }

    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Recipe
    {
        $recipe = new Recipe();

        foreach ($this->locales->get() as $locale) {
            $recipe->addTranslation(new RecipeTranslation()->setLocale($locale));
        }

        return $recipe;
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
            ->setEntityLabelInSingular('admin.recipe.dashboard.singular')
            ->setEntityLabelInPlural('admin.recipe.dashboard.plural')
            ->setPageTitle('index', 'admin.recipe.dashboard.index')
            ->setPageTitle('new', 'admin.recipe.dashboard.create')
            ->setPageTitle('edit', 'admin.recipe.dashboard.edit')
            ->setPageTitle('detail', 'admin.recipe.dashboard.detail')
            ->addFormTheme('@JoliMediaEasyAdmin/form/form_theme.html.twig')
            ->addFormTheme('admin/form/text_editor_theme.html.twig');
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isFeatured', 'admin.post.is_featured'));
    }

    private function getIndexFields(): \Generator
    {
        yield BooleanField::new('active', 'admin.post.active');

        yield BooleanField::new('isFeatured', 'admin.post.is_featured');

        yield TextField::new('title', 'admin.global.title');

        yield ArrayField::new('tags', 'admin.tag.dashboard.plural');

        yield AssociationField::new('category', 'admin.category.dashboard.singular')
            ->formatValue(static fn (?Category $category): string => $category?->getTitle() ?? '-');

        yield IntegerField::new('servings', 'admin.recipe.servings.label');

        yield DateField::new('createdAt', 'admin.global.created_at');

        yield DateField::new('updatedAt', 'admin.global.updated_at');
    }

    private function getFormFields(): \Generator
    {
        yield BooleanField::new('active', 'admin.post.active');

        yield BooleanField::new('isFeatured', 'admin.post.is_featured');

        yield IntegerField::new('preparationTime', 'admin.recipe.preparation_time.label');

        yield IntegerField::new('cookingTime', 'admin.recipe.cooking_time.label');

        yield IntegerField::new('servings', 'admin.recipe.servings.label');

        yield ChoiceField::new('difficulty', 'admin.recipe.difficulty.label')
            ->setChoices(Difficulty::cases())
            ->renderExpanded()
            ->renderAsBadges([
                'easy' => 'success',
                'medium' => 'warning',
                'advanced' => 'danger',
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
            ->setEntryType(RecipeTranslationType::class)
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

        yield CollectionField::new('ingredientGroups', 'admin.ingredient_group.dashboard.plural')
            ->setEntryType(IngredientGroupType::class)
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

        yield CollectionField::new('narrativeSections', 'admin.post_section.title')
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

        yield CollectionField::new('steps', 'admin.recipe_step.dashboard.plural')
            ->setEntryType(RecipeStepType::class)
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
