<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\PostSectionMedia;
use App\Entity\PostSectionMediaTranslation;
use JoliCode\MediaBundle\Bridge\EasyAdmin\Form\Type\MediaChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<PostSectionMediaType>
 */
class PostSectionMediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, [
                'label' => 'admin.global.position',
                'required' => true,
            ])
            ->add('media', MediaChoiceType::class, [
                'label' => 'admin.global.media.file',
                'required' => false,
                'translation_domain' => 'admin',
            ])
            ->add('translations', CollectionType::class, [
                'label' => 'admin.global.translations',
                'entry_type' => PostSectionMediaTranslationType::class,
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
            'data_class' => PostSectionMedia::class,
            'supported_locales' => [],
            'translation_domain' => 'admin',
            'empty_data' => function (FormInterface $form) {
                $entity = new PostSectionMedia();
                $locales = $form->getConfig()->getOption('supported_locales');
                foreach ($locales as $locale) {
                    $entity->addTranslation((new PostSectionMediaTranslation())->setLocale($locale));
                }

                return $entity;
            },
        ]);
    }
}
