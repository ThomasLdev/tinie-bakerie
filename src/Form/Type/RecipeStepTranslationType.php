<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\RecipeStepTranslation;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RecipeStepTranslationType extends PostSectionTranslationType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('tipText', TextareaType::class, [
                'label' => 'admin.recipe_step.tip_text.label',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'empty_data' => '',
                'help' => 'admin.recipe_step.tip_text.help',
            ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', RecipeStepTranslation::class);
    }
}
