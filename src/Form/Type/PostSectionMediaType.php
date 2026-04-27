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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<PostSectionMediaType>
 */
class PostSectionMediaType extends AbstractType
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
                'entry_type' => PostSectionMediaTranslationType::class,
                'entry_options' => [
                    'supported_locales' => $supportedLocales,
                ],
                'required' => true,
                'by_reference' => false,
                'allow_add' => false,
                'allow_delete' => false,
            ]);

        // Seed a PostSectionMedia with one translation per supported locale so
        // the prototype (and any new collection entry) renders translation
        // inputs. The inner translations CollectionType has allow_add: false,
        // so without seeding the prototype renders zero translation rows.
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($supportedLocales): void {
            $data = $event->getData();

            if (!$data instanceof PostSectionMedia) {
                $data = new PostSectionMedia();
                $event->setData($data);
            }

            if ($data->getTranslations()->isEmpty()) {
                foreach ($supportedLocales as $locale) {
                    $data->addTranslation(new PostSectionMediaTranslation()->setLocale($locale));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostSectionMedia::class,
            'supported_locales' => [],
            'translation_domain' => 'admin',
            'empty_data' => static function (FormInterface $form): PostSectionMedia {
                $entity = new PostSectionMedia();
                /** @var array<string> $locales */
                $locales = $form->getConfig()->getOption('supported_locales');
                foreach ($locales as $locale) {
                    $entity->addTranslation(new PostSectionMediaTranslation()->setLocale($locale));
                }

                return $entity;
            },
        ]);
    }
}
