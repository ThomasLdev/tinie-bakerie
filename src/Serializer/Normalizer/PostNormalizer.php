<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Services\Locale\Locales;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class PostNormalizer implements NormalizerInterface
{
    public function __construct(private Locales $locales)
    {
    }

    /**
     * @param Post $data
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $targetLocale = $context['meilisearch_locale'] ?? $this->locales->getDefault();
        $translation = $this->getTranslation($data, $targetLocale);

        if (null === $translation) {
            return [];
        }

        $category = $data->getCategory();
        $categoryTranslation = $this->getCategoryTranslation($category, $targetLocale);

        $tags = $this->getTagsForLocale($data, $targetLocale);

        return [
            'id' => $data->getId(),
            'title' => $translation->getTitle(),
            'slug' => $translation->getSlug(),
            'excerpt' => strip_tags($translation->getExcerpt()),
            'metaDescription' => $translation->getMetaDescription(),
            'categoryId' => $category?->getId(),
            'categoryTitle' => $categoryTranslation?->getTitle() ?? '',
            'categorySlug' => $categoryTranslation?->getSlug() ?? '',
            'tags' => $tags,
            'difficulty' => $data->getDifficulty()->value,
            'readingTime' => $data->getReadingTime(),
            'cookingTime' => $data->getCookingTime(),
            'isActive' => $data->isActive(),
            'createdAt' => $data->getCreatedAt()?->getTimestamp(),
            'locale' => $targetLocale,
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

        return null;
    }

    private function getCategoryTranslation(?Category $category, string $locale): ?object
    {
        if (!$category instanceof Category) {
            return null;
        }

        foreach ($category->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    private function getTagsForLocale(Post $post, string $locale): array
    {
        $tags = [];

        foreach ($post->getTags() as $tag) {
            foreach ($tag->getTranslations() as $translation) {
                if ($translation->getLocale() === $locale) {
                    $tags[] = $translation->getTitle();

                    break;
                }
            }
        }

        return $tags;
    }
}
