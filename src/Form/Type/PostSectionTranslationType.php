<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\PostSectionTranslation;
use App\Services\Locale\Locales;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\TextEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<PostSectionTranslationType>
 */
class PostSectionTranslationType extends AbstractType
{
    public function __construct(private readonly Locales $locales)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = $this->locales->get();

        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => array_combine($locales, $locales),
                'label' => 'admin.global.locale',
                'required' => true,
                'disabled' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'admin.post_section.section_title',
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'empty_data' => '',
            ])
            ->add('content', TextEditorType::class, [
                'label' => 'admin.post_section.content',
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'empty_data' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostSectionTranslation::class,
            'translation_domain' => 'admin',
        ]);
    }
}
