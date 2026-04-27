<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\PostSection;
use App\Entity\PostSectionTranslation;
use App\Services\PostSection\Enum\PostSectionType as PostSectionTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<PostSectionType>
 */
class PostSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, [
                'label' => 'admin.global.position',
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control',
                ],
                'empty_data' => '0',
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'admin.post_section.layout.label',
                'choices' => [
                    'admin.post_section.layout.default' => PostSectionTypeEnum::Default,
                    'admin.post_section.layout.two_columns' => PostSectionTypeEnum::TwoColumns,
                    'admin.post_section.layout.two_columns_media_left' => PostSectionTypeEnum::TwoColumnsMediaLeft,
                ],
                'choice_value' => static fn (?PostSectionTypeEnum $type): ?string => $type?->value,
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
                    'supported_locales' => $options['supported_locales'],
                ],
                'required' => true,
                'by_reference' => false,
                'allow_add' => false,
                'allow_delete' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostSection::class,
            'supported_locales' => [],
            'translation_domain' => 'admin',
            'empty_data' => static function (FormInterface $form): PostSection {
                $entity = new PostSection();
                /** @var array<string> $locales */
                $locales = $form->getConfig()->getOption('supported_locales');
                foreach ($locales as $locale) {
                    $entity->addTranslation(new PostSectionTranslation()->setLocale($locale));
                }

                return $entity;
            },
        ]);

        $resolver->setAllowedTypes('supported_locales', 'array');
    }
}
