<?php

namespace App\Services\Post;

use App\Entity\Post;

class PostDTOFactory
{
    public static function create(Post $post): PostDTO
    {
        $translation = $post->getTranslations()->first();
        $categoryTranslation = $post->getCategory()?->getTranslations()->first();

        return new PostDTO(
            $post->getId(),
            $translation->getTitle(),
            $translation->getSlug(),
            $categoryTranslation?->getName(),
            $categoryTranslation?->getSlug(),
            $post->getMedia(),
            $post->getTags(),
            $post->getSections(),
            $post->getCreatedAt(),
            $post->getUpdatedAt()
        );
    }
}

