<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\PostTranslation;
use App\Form\Trait\LocalizedFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<PostTranslationType>
 */
class PostTranslationType extends AbstractType
{
    use LocalizedFormType;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => $this->getLocales($options['supported_locales']),
                'label' => 'admin.global.locale',
                'required' => true,
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
            ])
            ->add('slug', TextType::class, [
                'label' => 'admin.global.slug.title',
                'disabled' => true,
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'help' => 'admin.global.slug.help',
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'admin.global.meta_description',
                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
            ->add('excerpt', TextareaType::class, [
                'label' => 'admin.global.excerpt',
                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'admin.post.notes.label',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'help' => 'admin.post.notes.help',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostTranslation::class,
            'supported_locales' => [],
        ]);

        $resolver->setAllowedTypes('supported_locales', 'array');
    }
}
