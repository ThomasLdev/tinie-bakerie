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
use JoliCode\MediaBundle\Model\Media;

#[AsEntityListener(event: Events::preRemove, entity: CategoryMedia::class)]
#[AsEntityListener(event: Events::preRemove, entity: PostMedia::class)]
#[AsEntityListener(event: Events::preRemove, entity: PostSectionMedia::class)]
class MediaAttachmentListener
{
    public function preRemove(MediaAttachment $entity, PreRemoveEventArgs $event): void
    {
        $media = $entity->getMedia();

        if (!$media instanceof Media) {
            return;
        }

        if ($media->isStored()) {
            $media->delete();
        }
    }
}
