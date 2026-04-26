<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\RecipeStep;
use App\Services\Recipe\Enum\StepTipType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RecipeStepType extends PostSectionType
{
    /**
     * @param array{supported_locales: array<string>} $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('tipType', ChoiceType::class, [
                'label' => 'admin.recipe_step.tip_type.label',
                'choices' => [
                    'admin.recipe_step.tip_type.tip' => StepTipType::Tip,
                    'admin.recipe_step.tip_type.warning' => StepTipType::Warning,
                ],
                'choice_value' => static fn (?StepTipType $type): ?string => $type?->value,
                'placeholder' => 'admin.recipe_step.tip_type.none',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->remove('translations')
            ->add('translations', CollectionType::class, [
                'label' => 'admin.global.translations',
                'entry_type' => RecipeStepTranslationType::class,
                'entry_options' => [
                    'supported_locales' => $options['supported_locales'],
                ],
                'required' => true,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
            ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', RecipeStep::class);
    }
}
