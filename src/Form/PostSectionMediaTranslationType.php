<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\PostSectionMediaTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostSectionMediaTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['hidde_locale']) {
            $builder
                ->add('locale', ChoiceType::class, [
                    'choices' => array_combine($options['supported_locales'], $options['supported_locales']),
                    'label' => 'admin.global.locale',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control',
                        'hidden' => $options['hidde_locale'] ?? false
                    ]
                ]);
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'admin.global.media.title',
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])
            ->add('alt', TextType::class, [
                'label' => 'admin.global.media.alt',
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostSectionMediaTranslation::class,
            'hidde_locale' => false,
            'supported_locales' => []
        ]);

        $resolver->setAllowedTypes('hidde_locale', 'bool');
        $resolver->setAllowedTypes('supported_locales', 'array');
    }
}
