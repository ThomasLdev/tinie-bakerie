<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Tag;
use App\Entity\TagTranslation;
use App\Form\Type\TagTranslationType;
use App\Services\Locale\Locales;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use JoliCode\MediaBundle\Bridge\EasyAdmin\Field\MediaChoiceField;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;

/**
 * @extends AbstractCrudController<Tag>
 */
class TagCrudController extends AbstractCrudController
{
    public function __construct(private readonly Locales $locales)
    {
    }

    #[\Override]
    public function configureAssets(Assets $assets): Assets
    {
        // this should not be needed, but there is a bug in EA with assets in nested forms
        // see https://github.com/EasyCorp/EasyAdminBundle/issues/6127
        $package = new PathPackage(
            '/bundles/jolimediaeasyadmin',
            new JsonManifestVersionStrategy(__DIR__ . '/../../../public/bundles/jolimediaeasyadmin/manifest.json'),
        );

        return $assets
            ->addCssFile($package->getUrl('joli-media-easy-admin.css'))
            ->addJsFile($package->getUrl('joli-media-easy-admin.js'));
    }

    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Tag
    {
        $tag = new Tag();

        foreach ($this->locales->get() as $locale) {
            $tag->addTranslation(new TagTranslation()->setLocale($locale));
        }

        return $tag;
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
            ->setEntityLabelInSingular('admin.tag.dashboard.singular')
            ->setEntityLabelInPlural('admin.tag.dashboard.plural')
            ->setPageTitle('index', 'admin.tag.dashboard.index')
            ->setPageTitle('new', 'admin.tag.dashboard.create')
            ->setPageTitle('edit', 'admin.tag.dashboard.edit')
            ->setPageTitle('detail', 'admin.tag.dashboard.detail')
            ->addFormTheme('@JoliMediaEasyAdmin/form/form_theme.html.twig');
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isFeatured', 'admin.tag.is_featured'));
    }

    private function getIndexFields(): \Generator
    {
        yield BooleanField::new('isFeatured', 'admin.tag.is_featured');

        yield TextField::new('title', 'admin.global.title')
            ->setColumns(12)
            ->setRequired(true);

        yield DateField::new('createdAt', 'admin.global.created_at');

        yield DateField::new('updatedAt', 'admin.global.updated_at');
    }

    private function getFormFields(): \Generator
    {
        yield BooleanField::new('isFeatured', 'admin.tag.is_featured');

        yield MediaChoiceField::new('image', 'admin.global.media.file')
            ->setFormTypeOptions(['required' => false])
            ->setColumns('col-12');

        yield CollectionField::new('translations', 'admin.global.translations')
            ->setEntryType(TagTranslationType::class)
            ->setFormTypeOptions([
                'by_reference' => false,
                'allow_add' => false,
                'allow_delete' => false,
                'entry_options' => [
                    'supported_locales' => $this->locales->get(),
                ],
            ])
            ->renderExpanded(false)
            ->setColumns('col-12');
    }
}
