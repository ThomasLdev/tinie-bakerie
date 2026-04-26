<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\RecipeTranslation;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RecipeTranslationType extends PostTranslationType
{
    /**
     * @param array{supported_locales: array<string>} $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('chefNoteTitle', TextType::class, [
                'label' => 'admin.recipe.chef_note_title.label',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'empty_data' => '',
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'admin.recipe.notes.label',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'empty_data' => '',
            ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', RecipeTranslation::class);
    }
}
