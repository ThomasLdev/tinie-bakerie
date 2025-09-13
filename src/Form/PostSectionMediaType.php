<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\PostSectionMedia;
use App\Services\Media\Enum\MediaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

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
            ->add('mediaFile', VichFileType::class, [
                'label' => 'admin.global.media.file',
                'required' => false,
                'allow_delete' => true,
                'download_label' => 'Télécharger le fichier',
                'asset_helper' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'admin.global.media.type',
                'choices' => array_combine(
                    array_map(static fn (MediaType $type) => $type->name, MediaType::cases()),
                    MediaType::cases(),
                ),
                'required' => true,
            ])
            ->add('translations', CollectionType::class, [
                'label' => 'admin.global.translations',
                'entry_type' => PostSectionMediaTranslationType::class,
                'entry_options' => [
                    'hidde_locale' => $options['hidde_locale'],
                    'supported_locales' => $options['supported_locales'],
                ],
                'required' => true,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostSectionMedia::class,
            'hidde_locale' => false,
            'supported_locales' => [],
        ]);
    }
}
