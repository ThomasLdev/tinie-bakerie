<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\CategoryMedia;
use App\Entity\Contracts\MediaAttachment;
use App\Entity\PostMedia;
use App\Entity\PostSectionMedia;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preRemove, entity: CategoryMedia::class)]
#[AsEntityListener(event: Events::preRemove, entity: PostMedia::class)]
#[AsEntityListener(event: Events::preRemove, entity: PostSectionMedia::class)]
class MediaAttachmentListener
{
    public function preRemove(MediaAttachment $entity, PreRemoveEventArgs $event): void
    {
        $media = $entity->getMedia();

        if (null === $media) {
            return;
        }

        if ($media->isStored()) {
            $media->delete();
        }
    }
}
