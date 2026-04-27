<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\IngredientGroup;
use App\Entity\IngredientGroupTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<IngredientGroup>
 */
class IngredientGroupType extends AbstractType
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
            ->add('translations', TranslationsCollectionType::class, [
                'entry_type' => IngredientGroupTranslationType::class,
                'translation_class' => IngredientGroupTranslation::class,
            ])
            ->add('ingredients', CollectionType::class, [
                'label' => 'admin.ingredient.dashboard.plural',
                'entry_type' => IngredientType::class,
                'required' => false,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IngredientGroup::class,
            'translation_domain' => 'admin',
        ]);
    }
}
