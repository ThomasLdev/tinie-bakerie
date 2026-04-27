<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\IngredientGroup;
use App\Entity\IngredientGroupTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<IngredientGroup>
 */
class IngredientGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array<string> $supportedLocales */
        $supportedLocales = $options['supported_locales'];

        $builder
            ->add('position', IntegerType::class, [
                'label' => 'admin.global.position',
                'required' => true,
                'empty_data' => '0',
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('translations', CollectionType::class, [
                'label' => 'admin.global.translations',
                'entry_type' => IngredientGroupTranslationType::class,
                'entry_options' => [
                    'supported_locales' => $supportedLocales,
                ],
                'required' => true,
                'by_reference' => false,
                'allow_add' => false,
                'allow_delete' => false,
            ])
            ->add('ingredients', CollectionType::class, [
                'label' => 'admin.ingredient.dashboard.plural',
                'entry_type' => IngredientType::class,
                'entry_options' => [
                    'supported_locales' => $supportedLocales,
                ],
                'required' => false,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
            ]);

        // Seed an IngredientGroup with one translation per supported locale so
        // the prototype (and any new collection entry) renders translation
        // inputs.
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($supportedLocales): void {
            $data = $event->getData();

            if (!$data instanceof IngredientGroup) {
                $data = new IngredientGroup();
                $event->setData($data);
            }

            if ($data->getTranslations()->isEmpty()) {
                foreach ($supportedLocales as $locale) {
                    $data->addTranslation(new IngredientGroupTranslation()->setLocale($locale));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IngredientGroup::class,
            'supported_locales' => [],
            'translation_domain' => 'admin',
            'empty_data' => static function (FormInterface $form): IngredientGroup {
                $entity = new IngredientGroup();
                /** @var array<string> $locales */
                $locales = $form->getConfig()->getOption('supported_locales');
                foreach ($locales as $locale) {
                    $entity->addTranslation(new IngredientGroupTranslation()->setLocale($locale));
                }

                return $entity;
            },
        ]);

        $resolver->setAllowedTypes('supported_locales', 'array');
    }
}
