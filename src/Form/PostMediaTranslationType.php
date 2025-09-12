<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\PostMediaTranslation;
use App\Form\Trait\LocalizedFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostMediaTranslationType extends AbstractType
{
    use LocalizedFormType;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['hidde_locale'] && is_array($options['supported_locales'])) {
            $builder
                ->add('locale', ChoiceType::class, [
                    'choices' => $this->getLocales($options['supported_locales']),
                    'label' => 'admin.global.locale',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control',
                        'hidden' => $options['hidde_locale'] ?? false,
                    ],
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
            'data_class' => PostMediaTranslation::class,
            'hidde_locale' => false,
            'supported_locales' => [],
        ]);

        $resolver->setAllowedTypes('hidde_locale', 'bool');
        $resolver->setAllowedTypes('supported_locales', 'array');
    }
}
