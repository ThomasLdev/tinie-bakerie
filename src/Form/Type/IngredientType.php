<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Ingredient;
use App\Entity\IngredientTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Ingredient>
 */
class IngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, [
                'label' => 'admin.global.position',
                'required' => true,
                'empty_data' => '0',
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('baseQuantity', NumberType::class, [
                'label' => 'admin.ingredient.base_quantity.label',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'help' => 'admin.ingredient.base_quantity.help',
            ])
            ->add('translations', TranslationsCollectionType::class, [
                'entry_type' => IngredientTranslationType::class,
                'translation_class' => IngredientTranslation::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ingredient::class,
            'translation_domain' => 'admin',
        ]);
    }
}
