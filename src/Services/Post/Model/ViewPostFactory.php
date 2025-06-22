<?php

namespace App\Services\Post\Model;

use App\Entity\Post;

class ViewPostFactory
{
    public static function create(Post $post): ViewPost
    {
        $translation = $post->getTranslations()->first();
        $categoryTranslation = $post->getCategory()?->getTranslations()->first();

        return new ViewPost(
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

