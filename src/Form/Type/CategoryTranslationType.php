<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\CategoryTranslation;
use App\Services\Locale\Locales;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<CategoryTranslationType>
 */
class CategoryTranslationType extends AbstractType
{
    public function __construct(private readonly Locales $locales)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = $this->locales->get();

        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => array_combine($locales, $locales),
                'label' => 'admin.global.locale',
                'required' => true,
                'disabled' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'admin.global.title',
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])
            ->add('metaTitle', TextType::class, [
                'label' => 'admin.global.meta_title',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'empty_data' => '',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'admin.global.title',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'empty_data' => '',
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'admin.global.meta_description',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'empty_data' => '',
            ])
            ->add('slug', TextType::class, [
                'label' => 'admin.global.slug.title',
                'disabled' => true,
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'help' => 'admin.global.slug.help',
            ])
            ->add('excerpt', TextareaType::class, [
                'label' => 'admin.global.excerpt',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'empty_data' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CategoryTranslation::class,
            'translation_domain' => 'admin',
        ]);
    }
}
