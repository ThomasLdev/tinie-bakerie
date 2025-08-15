<?php

declare(strict_types=1);

namespace App\Services\Fixtures;

use App\DataFixtures\Model\FileModel;
use App\Entity\Contracts\MediaEntityInterface;
use App\Services\Media\Enum\MediaType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class MediaFileProcessor
{
    public function __construct()
    {
    }

    public function process(MediaEntityInterface $entity, FileModel $fileModel): void
    {
        //        $entity->setMediaName($fileModel->getFileName());
        //        $entity->setType($this->getFileType($fileModel->getFileName()));
        //        $entity->setMediaFile($this->getUploadedFile($fileModel));
    }
}
