<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

use JoliCode\MediaBundle\Model\Media;

/**
 * Represents an entity that contains a media file attachment with metadata.
 */
interface MediaAttachment
{
    public function getMedia(): ?Media;

    public function setMedia(?Media $media): self;
}
