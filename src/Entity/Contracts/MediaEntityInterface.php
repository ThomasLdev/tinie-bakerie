<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

use App\Services\Media\Enum\MediaType;
use Symfony\Component\HttpFoundation\File\File;

interface MediaEntityInterface
{
    public function getMediaFile(): ?File;

    public function setMediaFile(?File $mediaFile = null): self;

    public function getMediaName(): ?string;

    public function setMediaName(string $mediaName): self;

    public function getType(): MediaType;

    public function setType(MediaType $type): self;
}
