<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\CategoryMedia;
use App\Entity\CategoryMediaTranslation;
use JoliCode\MediaBundle\Bridge\EasyAdmin\Form\Type\MediaChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<CategoryMediaType>
 */
class CategoryMediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, [
                'label' => 'admin.global.position',
                'required' => true,
                'attr' => ['min' => 0],
                'empty_data' => '0',
            ])
            ->add('media', MediaChoiceType::class, [
                'label' => 'admin.global.media.file',
                'required' => false,
                'translation_domain' => 'admin',
            ])
            ->add('translations', TranslationsCollectionType::class, [
                'entry_type' => CategoryMediaTranslationType::class,
                'translation_class' => CategoryMediaTranslation::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CategoryMedia::class,
            'translation_domain' => 'admin',
        ]);
    }
}
