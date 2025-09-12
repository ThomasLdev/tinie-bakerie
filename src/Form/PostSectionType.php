<?php

namespace App\Form;

use App\Entity\PostSection;
use App\Services\PostSection\Enum\PostSectionType as PostSectionTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control',
                ],
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Default' => PostSectionTypeEnum::Default,
                    'Two Columns' => PostSectionTypeEnum::TwoColumns,
                    'Two Columns Media Left' => PostSectionTypeEnum::TwoColumnsMediaLeft,
                ],
                'choice_value' => function (?PostSectionTypeEnum $type) {
                    return $type?->value;
                },
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => true,
            ])
            ->add('media', CollectionType::class, [
                'label' => 'admin.global.media.label',
                'required' => false,
                'entry_type' => PostSectionMediaType::class,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'entry_options' => [
                    'supported_locales' => $options['supported_locales'],
                ],
            ])
            ->add('translations', CollectionType::class, [
                'label' => 'admin.global.translations',
                'entry_type' => PostSectionTranslationType::class,
                'entry_options' => [
                    'hidde_locale' => $options['hidde_locale'],
                    'supported_locales' => $options['supported_locales'],
                ],
                'required' => true,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostSection::class,
            'hidde_locale' => false,
            'supported_locales' => [],
        ]);

        $resolver->setAllowedTypes('hidde_locale', 'bool');
        $resolver->setAllowedTypes('supported_locales', 'array');
    }
}
