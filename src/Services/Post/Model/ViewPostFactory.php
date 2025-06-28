<?php

namespace App\Services\Post\Model;

use App\Entity\Post;
use App\Services\ViewModelFactoryInterface;
use DateTime;

readonly class ViewPostFactory implements ViewModelFactoryInterface
{
    public function create(Post $entity): ViewPost
    {
        $translation = $entity->getTranslations()->first() ?: null;
        $categoryTranslation = $entity->getCategory()?->getTranslations()->first() ?: null;

        return new ViewPost(
            $translation?->getTitle() ?? '',
            $translation?->getSlug() ?? '',
            $categoryTranslation?->getName() ?? '',
            $categoryTranslation?->getSlug() ?? '',
            $entity->getMedia(),
            $entity->getTags(),
            $entity->getSections(),
            $entity->getCreatedAt() ?? new DateTime(),
            $entity->getUpdatedAt() ?? new DateTime()
        );
    }
}
