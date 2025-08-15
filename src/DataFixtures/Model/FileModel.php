<?php

declare(strict_types=1);

namespace App\DataFixtures\Model;

use App\Services\Media\Enum\MediaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileModel
{
    public function __construct(
        private string $name,
        private UploadedFile $file,
        private MediaType $type,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    public function getType(): MediaType
    {
        return $this->type;
    }
}
