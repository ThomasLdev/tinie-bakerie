<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

use App\Services\Media\Enum\MediaType;

/**
 * Represents an entity that contains a media file attachment with metadata.
 */
interface MediaAttachment
{
    public function getMediaPath(): ?string;

    public function setMediaPath(?string $mediaPath): self;

    public function getType(): MediaType;

    public function setType(MediaType $type): self;
}
