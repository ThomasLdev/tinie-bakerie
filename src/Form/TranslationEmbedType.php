<?php

namespace App\Form;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Translation;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationEmbedType extends AbstractType
{
    public function __construct(
        #[Autowire(param: 'app.supported_locales')] private readonly string $supportedLocales,
        #[Autowire(param: 'default_locale')] private readonly string $defaultLocale,
        private readonly EntityManagerInterface $entityManager,
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
            $fieldType = $configuration['fieldType'] ?? TextType::class;

            foreach (explode('|', $this->supportedLocales) as $locale) {
                $builder
                    ->add(
                        sprintf('%s_%s', $key, $locale),
                        $fieldType,
                        [
                            'label' => $locale,
                            'required' => $required,
                            'mapped' => false,
                            'attr' => ['rows' => $rows]
                        ]
                    );
            }
        }

        // Add event listeners to handle translation data
        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'onPostSetData']);
//        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    public function onPostSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $parentForm = $form->getParent();

        // Get the entity from the parent form (the Post entity)
        $entity = $parentForm?->getData();

        $options = $form->getConfig()->getOptions();
        $properties = $options['properties'] ?? [];

        if (!$entity || !method_exists($entity, 'getId') || !$entity->getId()) {
            return;
        }

        $translationRepo = $this->entityManager->getRepository(Translation::class);

        foreach ($properties as $fieldName => $configuration) {
            foreach (explode('|', $this->supportedLocales) as $locale) {
//                if ($locale === $this->defaultLocale) {
//                    continue;
//                }

                // Load existing translation
                $translation = $translationRepo->findOneBy([
                    'locale' => $locale,
                    'field' => $fieldName,
                    'objectClass' => get_class($entity),
                    'foreignKey' => $entity->getId()
                ]);

                $fieldKey = sprintf('%s_%s', $fieldName, $locale);
                if ($translation && $form->has($fieldKey)) {
                    $form->get($fieldKey)->setData($translation->getContent());
                }
            }
        }
    }

    public function onPostSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $parentForm = $form->getParent();

        // Get the entity from the parent form (the Post entity)
        $entity = $parentForm ? $parentForm->getData() : null;

        $options = $form->getConfig()->getOptions();
        $properties = $options['properties'] ?? [];

        if (!$entity || !method_exists($entity, 'getId')) {
            return;
        }

        $translationRepo = $this->entityManager->getRepository(Translation::class);

        foreach ($properties as $fieldName => $configuration) {
            foreach (explode('|', $this->supportedLocales) as $locale) {
                if ($locale === $this->defaultLocale) {
                    continue;
                }

                $fieldKey = sprintf('%s_%s', $fieldName, $locale);
                if ($form->has($fieldKey)) {
                    $value = $form->get($fieldKey)->getData();
                    if ($value !== null && $value !== '') {
                        $this->saveTranslation($translationRepo, $entity, $fieldName, $locale, $value);
                    }
                }
            }
        }
    }

    private function saveTranslation($repo, $entity, string $field, string $locale, string $value): void
    {
        $translation = $repo->findOneBy([
            'locale' => $locale,
            'field' => $field,
            'objectClass' => get_class($entity),
            'foreignKey' => $entity->getId()
        ]);

        if (!$translation) {
            $translation = new Translation();
            $translation->setLocale($locale);
            $translation->setField($field);
            $translation->setObjectClass(get_class($entity));
            $translation->setForeignKey($entity->getId());
        }

        $translation->setContent($value);
        $this->entityManager->persist($translation);
        $this->entityManager->flush();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'properties' => [],
        ]);
    }
}
