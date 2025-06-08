<?php

namespace App\Services\Post;

use App\Entity\Post;

class PostDTOFactory
{
    public static function create(Post $post): PostDTO
    {
        $translation = $post->getTranslations()->first();

        return new PostDTO(
            $post->getId(),
            $translation->getTitle(),
            $translation->getSlug(),
            $post->getCategory()?->getTranslations()->first(),
            $post->getImageName(),
            $post->getTags(),
            $translation->getSections(),
            $post->getCreatedAt(),
            $post->getUpdatedAt()
        );
    }
}

