<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Post;
use App\Entity\PostTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostTranslationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = $options['locales'] ?? ['en'];

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($locales) {
            $this->setTranslatableFields($event, $locales);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($locales) {
            $this->setTranslatableFormData($event, $locales);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'locales' => ['en'],
        ]);

        $resolver->setAllowedTypes('locales', 'array');
    }

    private function setTranslatableFields(FormEvent $event, array $locales): void
    {
        $translations = $event->getData();
        $form = $event->getForm();

        $translationData = [];
        if ($translations && is_iterable($translations)) {
            foreach ($translations as $translation) {
                $fieldName = $this->formatFieldName($translation->getField(), $translation->getLocale());
                $translationData[$fieldName] = $translation->getContent();
            }
        }

        foreach ($locales as $locale) {
            $form
                ->add($this->formatFieldName('title', $locale), TextType::class, [
                    'label' => $this->formatFieldLabel('Title', $locale),
                    'mapped' => false,
                    'data' => $translationData[$this->formatFieldName('title', $locale)] ?? '',
                ])
                ->add($this->formatFieldName('metaTitle', $locale), TextType::class, [
                    'label' => $this->formatFieldLabel('Meta Title', $locale),
                    'mapped' => false,
                    'data' => $translationData[$this->formatFieldName('metaTitle', $locale)] ?? '',
                ])
                ->add($this->formatFieldName('metaDescription', $locale), TextareaType::class, [
                    'label' => $this->formatFieldLabel('Meta Description', $locale),
                    'mapped' => false,
                    'data' => $translationData[$this->formatFieldName('metaDescription', $locale)] ?? '',
                ])
                ->add($this->formatFieldName('excerpt', $locale), TextareaType::class, [
                    'label' => $this->formatFieldLabel('Excerpt', $locale),
                    'mapped' => false,
                    'data' => $translationData[$this->formatFieldName('excerpt', $locale)] ?? '',
                ]);
        }
    }

    private function setTranslatableFormData(FormEvent $event, array $locales): void
    {
        $post = $event->getForm()->getParent()?->getData();
        $submittedData = $event->getData();

        if (!$post instanceof Post || !is_array($submittedData)) {
            return;
        }

        $fields = ['title', 'metaTitle', 'metaDescription', 'excerpt'];

        foreach ($locales as $locale) {
            foreach ($fields as $field) {
                $fieldName = $this->formatFieldName($field, $locale);

                if (!isset($submittedData[$fieldName])) {
                    continue;
                }

                $value = $submittedData[$fieldName];
                $this->updateTranslation($post, $field, $locale, $value);
            }
        }
    }

    private function updateTranslation(Post $post, string $field, string $locale, ?string $value): void
    {
        $existingTranslation = $this->findTranslation($post, $field, $locale);

        if (empty($value) && null !== $existingTranslation) {
            $post->getTranslations()->removeElement($existingTranslation);

            return;
        }

        if (null !== $existingTranslation) {
            $existingTranslation->setContent($value);

            return;
        }

        $newTranslation = new PostTranslation($locale, $field, $value);
        $newTranslation->setObject($post);
        $post->addTranslation($newTranslation);
    }

    private function findTranslation(Post $post, string $field, string $locale): ?PostTranslation
    {
        foreach ($post->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale && $translation->getField() === $field) {
                return $translation;
            }
        }
        return null;
    }

    private function formatFieldName(string $field, string $locale): string
    {
        return sprintf('%s_%s', $field, $locale);
    }

    private function formatFieldLabel(string $fieldLabel, string $locale): string
    {
        return sprintf('%s (%s)', $fieldLabel, $locale);
    }
}
