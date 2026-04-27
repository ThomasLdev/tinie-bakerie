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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<PostSectionType>
 */
class PostSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array<string> $supportedLocales */
        $supportedLocales = $options['supported_locales'];
        /** @var class-string<PostSection> $dataClass */
        $dataClass = $options['data_class'];
        /** @var class-string<PostSectionTranslation> $translationClass */
        $translationClass = $options['translation_class'];

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
                    'supported_locales' => $supportedLocales,
                ],
            ])
            ->add('translations', CollectionType::class, [
                'label' => 'admin.global.translations',
                'entry_type' => PostSectionTranslationType::class,
                'entry_options' => [
                    'supported_locales' => $supportedLocales,
                ],
                'required' => true,
                'by_reference' => false,
                'allow_add' => false,
                'allow_delete' => false,
            ]);

        // Seed a section with one translation per supported locale so the
        // prototype (and any new collection entry) renders translation inputs.
        // The inner translations CollectionType has allow_add: false, so
        // without seeding the prototype renders zero translation rows.
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($supportedLocales, $dataClass, $translationClass): void {
            $data = $event->getData();

            if (!$data instanceof $dataClass) {
                $data = new $dataClass();
                $event->setData($data);
            }

            if ($data->getTranslations()->isEmpty()) {
                foreach ($supportedLocales as $locale) {
                    $data->addTranslation(new $translationClass()->setLocale($locale));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostSection::class,
            'translation_class' => PostSectionTranslation::class,
            'supported_locales' => [],
            'translation_domain' => 'admin',
            'empty_data' => static function (FormInterface $form): PostSection {
                /** @var class-string<PostSection> $dataClass */
                $dataClass = $form->getConfig()->getOption('data_class');
                /** @var class-string<PostSectionTranslation> $translationClass */
                $translationClass = $form->getConfig()->getOption('translation_class');
                /** @var array<string> $locales */
                $locales = $form->getConfig()->getOption('supported_locales');

                $entity = new $dataClass();
                foreach ($locales as $locale) {
                    $entity->addTranslation(new $translationClass()->setLocale($locale));
                }

                return $entity;
            },
        ]);

        $resolver->setAllowedTypes('supported_locales', 'array');
        $resolver->setAllowedTypes('translation_class', 'string');
    }
}
