<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Services\Locale\Locales;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class PostNormalizer implements NormalizerInterface
{
    public function __construct(
        private Locales $locales,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param Post $data
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $targetLocale = $context['meilisearch_locale'] ?? $this->locales->getDefault();
        $translation = $this->getTranslation($data, $targetLocale);
        $categoryTranslation = $this->getCategoryTranslation($data->getCategory(), $targetLocale);

        if (!$translation instanceof PostTranslation || null === $categoryTranslation) {
            $this->logger->warning('Post had no translation or category translation, skipping indexation', [
                'locale' => $targetLocale,
                'data' => $data,
            ]);

            return [];
        }

        $tags = $this->getTagsForLocale($data, $targetLocale);

        return [
            'id' => $data->getId(),
            'title' => $translation->getTitle(),
            'excerpt' => strip_tags($translation->getExcerpt()),
            'categoryTitle' => $categoryTranslation->getTitle(),
            'tags' => $tags,
            'difficulty' => $data->getDifficulty()->value,
            'readingTime' => $data->getReadingTime(),
            'cookingTime' => $data->getCookingTime(),
            'isActive' => $data->isActive(),
            'createdAt' => $data->getCreatedAt()?->getTimestamp(),
            'locale' => $targetLocale,
            'url' => $this->getPostUrl($targetLocale, $translation->getSlug(), $categoryTranslation->getSlug()),
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

    private function getPostUrl(string $targetLocale, string $postSlug, string $categorySlug): string
    {
        return $this->urlGenerator->generate(
            'app_post_show',
            [
                '_locale' => $targetLocale,
                'categorySlug' => $categorySlug,
                'postSlug' => $postSlug,
            ],
        );
    }
}
