<?php

namespace App\Form;

use App\Entity\PostSection;
use App\Entity\PostSectionTranslation;
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
                    'class' => 'form-control'
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
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('translations', CollectionType::class, [
                'label' => 'admin.global.translations',
                'entry_type' => PostSectionTranslationType::class,
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostSection::class,
        ]);
    }
}
