<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Post;
use App\Entity\PostTranslation;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PostNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
        private readonly string $defaultLocale = 'en',
    ) {
    }

    /**
     * @param Post $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        // Get the translation for the current locale or default locale
        $translation = $this->getTranslation($object, $this->defaultLocale);
        
        if (!$translation) {
            return [];
        }

        $category = $object->getCategory();
        $categoryTranslation = $category?->getTranslations()->first();

        $tags = [];
        foreach ($object->getTags() as $tag) {
            $tagTranslation = $tag->getTranslations()->first();
            if ($tagTranslation) {
                $tags[] = $tagTranslation->getTitle();
            }
        }

        return [
            'id' => $object->getId(),
            'title' => $translation->getTitle(),
            'slug' => $translation->getSlug(),
            'excerpt' => strip_tags($translation->getExcerpt()),
            'metaDescription' => $translation->getMetaDescription(),
            'categoryId' => $category?->getId(),
            'categoryTitle' => $categoryTranslation?->getTitle(),
            'categorySlug' => $categoryTranslation?->getSlug(),
            'tags' => $tags,
            'difficulty' => $object->getDifficulty()->value,
            'readingTime' => $object->getReadingTime(),
            'cookingTime' => $object->getCookingTime(),
            'isActive' => $object->isActive(),
            'createdAt' => $object->getCreatedAt()?->getTimestamp(),
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Post;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Post::class => true,
        ];
    }

    private function getTranslation(Post $post, string $locale): ?PostTranslation
    {
        foreach ($post->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        // Fallback to first translation if locale not found
        return $post->getTranslations()->first() ?: null;
    }
}
