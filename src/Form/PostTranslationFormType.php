<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostTranslationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $options['locale'] ?? 'en';

        $builder
            ->add('title', TextType::class, [
                'label' => 'admin.global.title' . ' (' . strtoupper($locale) . ')',
                'required' => $locale === 'en', // Required for default locale
                'mapped' => false,
                'data' => $this->getTranslatedValue($options['data'] ?? null, 'title', $locale),
            ])
            ->add('metaTitle', TextType::class, [
                'label' => 'admin.post.meta_title' . ' (' . strtoupper($locale) . ')',
                'required' => false,
                'mapped' => false,
                'attr' => ['maxlength' => 60],
                'data' => $this->getTranslatedValue($options['data'] ?? null, 'metaTitle', $locale),
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'admin.post.meta_description' . ' (' . strtoupper($locale) . ')',
                'required' => false,
                'mapped' => false,
                'attr' => ['rows' => 3],
                'data' => $this->getTranslatedValue($options['data'] ?? null, 'metaDescription', $locale),
            ])
            ->add('excerpt', TextareaType::class, [
                'label' => 'admin.post.excerpt' . ' (' . strtoupper($locale) . ')',
                'required' => false,
                'mapped' => false,
                'attr' => ['rows' => 4],
                'data' => $this->getTranslatedValue($options['data'] ?? null, 'excerpt', $locale),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'locale' => 'en',
        ]);

        $resolver->setAllowedTypes('locale', 'string');
    }

    private function getTranslatedValue(?Post $post, string $field, string $locale): ?string
    {
        if (!$post) {
            return null;
        }

        // For the default locale, get the value directly
        if ($locale === 'en') { // Adjust based on your default locale
            $getter = 'get' . ucfirst($field);
            return method_exists($post, $getter) ? $post->$getter() : null;
        }

        // For other locales, look in translations
        foreach ($post->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale && $translation->getField() === $field) {
                return $translation->getContent();
            }
        }

        return null;
    }
}
