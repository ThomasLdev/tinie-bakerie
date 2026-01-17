<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\PostMediaTranslation;
use App\Form\Type\Trait\LocalizedFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<PostMediaTranslationType>
 */
class PostMediaTranslationType extends AbstractType
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
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'admin.global.media.title',
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'empty_data' => '',
            ])
            ->add('alt', TextType::class, [
                'label' => 'admin.global.media.alt',
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'empty_data' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostMediaTranslation::class,
            'supported_locales' => [],
        ]);

        $resolver->setAllowedTypes('supported_locales', 'array');
    }
}
