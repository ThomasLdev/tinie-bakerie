<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\PostMedia;
use App\Entity\PostMediaTranslation;
use JoliCode\MediaBundle\Bridge\EasyAdmin\Form\Type\MediaChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<PostMediaType>
 */
class PostMediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array<string> $supportedLocales */
        $supportedLocales = $options['supported_locales'];

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
            ->add('translations', CollectionType::class, [
                'label' => 'admin.global.translations',
                'entry_type' => PostMediaTranslationType::class,
                'entry_options' => [
                    'supported_locales' => $supportedLocales,
                ],
                'required' => true,
                'by_reference' => false,
                'allow_add' => false,
                'allow_delete' => false,
            ]);

        // Seed a PostMedia with one translation per supported locale so the
        // prototype (and any new collection entry) renders translation inputs.
        // Without this, the inner translations CollectionType has allow_add:
        // false and zero entries on a fresh PostMedia, leaving no inputs.
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($supportedLocales): void {
            $data = $event->getData();

            if (!$data instanceof PostMedia) {
                $data = new PostMedia();
                $event->setData($data);
            }

            if ($data->getTranslations()->isEmpty()) {
                foreach ($supportedLocales as $locale) {
                    $data->addTranslation(new PostMediaTranslation()->setLocale($locale));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostMedia::class,
            'supported_locales' => [],
            'translation_domain' => 'admin',
            'empty_data' => static function (FormInterface $form): PostMedia {
                $entity = new PostMedia();
                /** @var array<string> $locales */
                $locales = $form->getConfig()->getOption('supported_locales');
                foreach ($locales as $locale) {
                    $entity->addTranslation(new PostMediaTranslation()->setLocale($locale));
                }

                return $entity;
            },
        ]);
    }
}
