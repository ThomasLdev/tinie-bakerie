<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\RecipeStep;
use App\Entity\RecipeStepTranslation;
use App\Services\Recipe\Enum\StepTipType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RecipeStepType extends PostSectionType
{
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
            ->add('translations', TranslationsCollectionType::class, [
                'entry_type' => RecipeStepTranslationType::class,
                'translation_class' => RecipeStepTranslation::class,
            ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', RecipeStep::class);
    }
}
