<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\IngredientTranslation;
use App\Form\Type\Trait\LocalizedFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<IngredientTranslation>
 */
class IngredientTranslationType extends AbstractType
{
    use LocalizedFormType;

    /**
     * @param array{supported_locales: array<string>} $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => $this->getLocales($options['supported_locales']),
                'label' => 'admin.global.locale',
                'required' => true,
                'disabled' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('name', TextType::class, [
                'label' => 'admin.ingredient.name.label',
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'empty_data' => '',
            ])
            ->add('unit', TextType::class, [
                'label' => 'admin.ingredient.unit.label',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'empty_data' => '',
                'help' => 'admin.ingredient.unit.help',
            ])
            ->add('quantityDisplay', TextType::class, [
                'label' => 'admin.ingredient.quantity_display.label',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'empty_data' => '',
                'help' => 'admin.ingredient.quantity_display.help',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IngredientTranslation::class,
            'supported_locales' => [],
            'translation_domain' => 'admin',
        ]);

        $resolver->setAllowedTypes('supported_locales', 'array');
    }
}
