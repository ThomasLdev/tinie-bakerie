<?php

namespace App\Form;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationEmbedType extends AbstractType
{
    public function __construct(
        #[Autowire(param: 'app.supported_locales')] private readonly string $supportedLocales,
        #[Autowire(param: 'default_locale')] private readonly string $defaultLocale,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $properties = $options['properties'] ?? [];

        if ([] === $properties) {
            return;
        }

        foreach ($properties as $key => $configuration) {
            $rows = $configuration['rows'] ?? 12;
            $required = $configuration['required'] ?? false;
            $formType = $configuration['formType'] ?? TextType::class;

            foreach (explode('|', $this->supportedLocales) as $locale) {
                $isDefault = $locale === $this->defaultLocale;

                $builder
                    ->add(
                        $isDefault ? $key : sprintf('%s_%s', $key, $locale),
                        $formType,
                        [
                            'label' => $locale,
                            'required' => $required,
                            'mapped' => $isDefault,
                            'attr' => ['rows' => $rows]
                        ]
                    );
            }
        }
    }

//    public function onPostSetData(FormEvent $event): void
//    {
//        $entity = $event->getData();
//        $form = $event->getForm();
//        $options = $form->getConfig()->getOptions();
//
//        if (!$entity || !method_exists($entity, 'getId') || !$entity->getId()) {
//            return;
//        }
//
//        /** @var TranslationRepository $translationRepo */
//        $translationRepo = $this->entityManager->getRepository(Translation::class);
//
//        foreach ($options['locales'] as $locale) {
//            if ($locale === $options['default_locale']) continue;
//
//            // Load existing translations
//            $titleTranslation = $translationRepo->findOneBy([
//                'locale' => $locale,
//                'field' => 'title',
//                'objectClass' => get_class($entity),
//                'foreignKey' => $entity->getId()
//            ]);
//
//            $contentTranslation = $translationRepo->findOneBy([
//                'locale' => $locale,
//                'field' => 'content',
//                'objectClass' => get_class($entity),
//                'foreignKey' => $entity->getId()
//            ]);
//
//            // Set form data
//            if ($titleTranslation) {
//                $form->get('title_' . $locale)->setData($titleTranslation->getContent());
//            }
//            if ($contentTranslation) {
//                $form->get('content_' . $locale)->setData($contentTranslation->getContent());
//            }
//        }
//    }
//
//    public function onPostSubmit(FormEvent $event): void
//    {
//        $entity = $event->getData();
//        $form = $event->getForm();
//        $options = $form->getConfig()->getOptions();
//
//        if (!$entity || !method_exists($entity, 'getId')) {
//            return;
//        }
//
//        /** @var TranslationRepository $translationRepo */
//        $translationRepo = $this->entityManager->getRepository(Translation::class);
//
//        foreach ($options['locales'] as $locale) {
//            if ($locale === $options['default_locale']) continue;
//
//            $titleValue = $form->get('title_' . $locale)->getData();
//            $contentValue = $form->get('content_' . $locale)->getData();
//
//            if (!empty($titleValue)) {
//                $this->saveTranslation($translationRepo, $entity, 'title', $locale, $titleValue);
//            }
//
//            if (!empty($contentValue)) {
//                $this->saveTranslation($translationRepo, $entity, 'content', $locale, $contentValue);
//            }
//        }
//
//        $this->entityManager->flush();
//    }
//
//    private function saveTranslation(TranslationRepository $repo, $entity, string $field, string $locale, string $value): void
//    {
//        $translation = $repo->findOneBy([
//            'locale' => $locale,
//            'field' => $field,
//            'objectClass' => get_class($entity),
//            'foreignKey' => $entity->getId()
//        ]);
//
//        if ($translation) {
//            $translation->setContent($value);
//        } else {
//            $repo->translate($entity, $field, $locale, $value);
//        }
//    }
//
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'properties' => [],
            'mapped' => false,
        ]);
    }
}
