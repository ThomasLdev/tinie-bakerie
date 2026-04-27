<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\RecipeStep;
use App\Entity\RecipeStepTranslation;
use App\Services\Recipe\Enum\StepTipType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
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
                'allow_add' => false,
                'allow_delete' => false,
            ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => RecipeStep::class,
            'empty_data' => static function (FormInterface $form): RecipeStep {
                $entity = new RecipeStep();
                /** @var array<string> $locales */
                $locales = $form->getConfig()->getOption('supported_locales');
                foreach ($locales as $locale) {
                    $entity->addTranslation(new RecipeStepTranslation()->setLocale($locale));
                }

                return $entity;
            },
        ]);
    }
}
